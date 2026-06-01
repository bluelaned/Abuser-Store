<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Variant;
use App\Models\Promo;
use App\Models\Transaction;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Midtrans\Snap;
use Midtrans\Config;

class PaymentController extends Controller
{
    public function process(Request $request)
    {
        \Log::info("--- CHECKOUT HIT ---", $request->all());

        // 0. Rate Limiting (Prevent Spam & Double Click)
        $userId = auth()->id() ?? request()->ip();
        $rateLimitKey = 'checkout-spam-limit:' . $userId;

        if (\Illuminate\Support\Facades\RateLimiter::tooManyAttempts($rateLimitKey, 1)) {
            \Log::warning('Checkout Spam Detected for User/IP: ' . $userId);
            return back()->with('error', 'Harap tunggu beberapa detik sebelum mencoba checkout lagi (Mencegah spam).');
        }

        \Illuminate\Support\Facades\RateLimiter::hit($rateLimitKey, 10); // Block for 10 seconds

        // 1. Ambil Data
        $variant = Variant::with('product')->find($request->variant_id);

        if (!$variant) {
            return back()->with('error', 'Produk tidak ditemukan.');
        }

        $method   = $request->payment_method;
        $email    = auth()->user()->email;
        $quantity = (int) $request->quantity;
        if ($quantity < 1) $quantity = 1;

        if ($variant->product->delivery_method === 'gift') {
            if (empty($request->gift_username)) {
                return back()->with('error', 'Silakan masukkan username target untuk gift.');
            }
            $quantity = 1;
        } else {
            // CEK KETERSEDIAAN STOK (Mencegah Overselling)
            $availableStock = \App\Models\VoucherCode::where('variant_id', $variant->id)
                                                     ->where('status', 'AVAILABLE')
                                                     ->count();
            if ($availableStock < $quantity) {
                return back()->with('error', 'Stok tidak mencukupi! Sisa stok saat ini: ' . $availableStock);
            }
        }

        // Use variant's own currency and price_amount
        $variantCurrency      = strtoupper($variant->currency ?? 'USD');
        $originalUnitAmount   = (float) ($variant->price_amount ?? $variant->price_usd ?? 0);
        $originalTotalAmount  = $originalUnitAmount * $quantity;

        // For backward-compat with Stripe (which needs USD-equivalent for display)
        $originalUnitPriceUSD = $variantCurrency === 'USD' ? $originalUnitAmount
            : ($variantCurrency === 'IDR' ? $originalUnitAmount / 15500 : $originalUnitAmount);
        $originalTotalUSD     = $originalUnitPriceUSD * $quantity;

        $discountNative = 0;
        $discountUSD    = 0;
        $promoCode  = null;
        $promoModel = null;

        if ($request->promo_code) {
            $promoModel = Promo::where('code', $request->promo_code)->first();
            if ($promoModel) {
                $isActive    = ($promoModel->is_active == 1 || is_null($promoModel->is_active));
                $isValidDate = true;
                if (!is_null($promoModel->valid_until)) {
                    $expiredDate = \Carbon\Carbon::parse($promoModel->valid_until)->endOfDay();
                    if ($expiredDate->isPast() && !$expiredDate->isToday()) $isValidDate = false;
                }
                if (!$isActive || !$isValidDate) {
                    return back()->with('error', 'Kode promo tidak aktif atau kedaluwarsa.');
                }
                if ($quantity < $promoModel->min_qty) {
                    return back()->with('error', 'Minimal pembelian ' . $promoModel->min_qty . ' produk untuk menggunakan promo ini.');
                }
                if ($promoModel->product_id && $promoModel->product_id != $variant->product_id) {
                    return back()->with('error', 'Promo ini tidak berlaku untuk produk ini.');
                }
                if ($promoModel->usage_limit_per_user > 0) {
                    $usedCount = Transaction::where('user_id', auth()->id())
                        ->where('promo_code', $promoModel->code)
                        ->whereIn('status', ['PAID', 'UNPAID'])->count();
                    if ($usedCount >= $promoModel->usage_limit_per_user) {
                        return back()->with('error', 'Anda sudah mencapai batas penggunaan promo ini.');
                    }
                }

                $promoCode = $promoModel->code;
                // Discount always stored in IDR, convert to native currency
                $rate = 15500;
                if ($promoModel->type == 'percent') {
                    $discountNative = $originalTotalAmount * ($promoModel->value / 100);
                    if ($promoModel->max_discount > 0) {
                        $maxNative = $variantCurrency === 'IDR' ? $promoModel->max_discount : $promoModel->max_discount / $rate;
                        if ($discountNative > $maxNative) $discountNative = $maxNative;
                    }
                } else {
                    $discountNative = $variantCurrency === 'IDR' ? $promoModel->value : $promoModel->value / $rate;
                }
                $discountUSD = $variantCurrency === 'IDR' ? $discountNative / $rate : $discountNative;
            }
        }

        $finalTotalNative = max(0.01, $originalTotalAmount - $discountNative);
        $finalTotalUSD    = max(0.01, $originalTotalUSD - $discountUSD);

        // 4. Route to payment gateway
        if (strtolower($method) === 'paypal') {
            return $this->processPaypal($variant, $originalUnitPriceUSD, $discountUSD, $finalTotalUSD, $quantity, $variantCurrency, $email, $promoCode);
        }

        if (strtolower($method) === 'crypto') {
            return $this->processCrypto($variant, $originalUnitPriceUSD, $discountUSD, $finalTotalUSD, $quantity, $email, $promoCode);
        }

        if (strtolower($method) === 'stripe') {
            return $this->processStripe($variant, $originalUnitPriceUSD, $discountUSD, $finalTotalUSD, $quantity, $variantCurrency, $email, $promoCode, $request->promo_code);
        }

        // Midtrans → always IDR
        $rate = 15500;
        $originalUnitPriceIDR = $variantCurrency === 'IDR' ? (int) $originalUnitAmount : (int) round($originalUnitPriceUSD * $rate);
        $discountIDR = $variantCurrency === 'IDR' ? (int) $discountNative : (int) round($discountUSD * $rate);

        return $this->processMidtrans($variant, $originalUnitPriceIDR, $discountIDR, $quantity, $email, $promoCode);
    }

    // ─────────────────────────────────────────────────────
    private function processMidtrans($variant, $originalUnitPriceIDR, $discountIDR, $quantity, $email, $promoCode)
    {
        Config::$serverKey    = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        $orderId      = 'INV-' . time() . '-' . rand(100, 999);
        $grossAmount  = ($originalUnitPriceIDR * $quantity) - $discountIDR;
        if ($grossAmount < 1) $grossAmount = 1;

        $itemDetails = [
            [
                'id'       => $variant->id,
                'price'    => $originalUnitPriceIDR,
                'quantity' => $quantity,
                'name'     => substr($variant->product->name . ' (' . $variant->duration . ')', 0, 50),
            ]
        ];

        if ($discountIDR > 0) {
            $itemDetails[] = [
                'id'       => 'DISC-' . $promoCode,
                'price'    => -$discountIDR,
                'quantity' => 1,
                'name'     => 'Promo: ' . $promoCode,
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $grossAmount,
            ],
            'customer_details' => ['email' => $email],
            'item_details'     => $itemDetails,
        ];

        try {
            $snapToken = Snap::getSnapToken($params);
            $baseUrl   = Config::$isProduction
                ? 'https://app.midtrans.com/snap/v2/vtweb/'
                : 'https://app.sandbox.midtrans.com/snap/v2/vtweb/';

            $checkoutUrl = $baseUrl . $snapToken;

            // ── Simpan transaksi ke database ──
            Transaction::create([
                'user_id'        => auth()->id(),
                'reference'      => $orderId,
                'merchant_ref'   => $orderId,
                'product_name'   => $variant->product->name . ' (' . $variant->duration . ')',
                'variant_id'     => $variant->id,
                'quantity'       => $quantity,
                'price'          => $grossAmount,
                'original_price' => $originalUnitPriceIDR * $quantity,
                'promo_code'     => $promoCode,
                'gift_username'  => $variant->product->delivery_method === 'gift' ? request('gift_username') : null,
                'customer_email' => $email,
                'status'         => 'UNPAID',
                'payment_method' => 'MIDTRANS',
                'checkout_url'   => $checkoutUrl,
            ]);

            self::sendAdminChannelNotification([
                'order_id'       => $orderId,
                'product_name'   => $variant->product->name . ' (' . $variant->duration . ')',
                'quantity'       => $quantity,
                'total'          => $grossAmount,
                'currency'       => 'IDR',
                'payment_method' => 'Midtrans',
                'customer_email' => $email,
                'discord_name'   => auth()->user()->name ?? null,
                'discord_id'     => auth()->user()->discord_id ?? null,
            ]);

            // Simpan data ke session untuk halaman sukses
            $this->storeOrderSession([
                'order_id'       => $orderId,
                'variant_id'     => $variant->id,
                'product_name'   => $variant->product->name . ' (' . $variant->duration . ')',
                'quantity'       => $quantity,
                'unit_price'     => $originalUnitPriceIDR / 15500,
                'discount'       => $discountIDR / 15500,
                'total'          => $grossAmount,
                'currency'       => 'IDR',
                'payment_method' => 'Midtrans (QRIS/BCA)',
                'customer_email' => $email,
                'promo_code'     => $promoCode,
                'paid_at'        => now()->format('d M Y, H:i'),
            ]);

            return redirect($checkoutUrl);

        } catch (\Exception $e) {
            return back()->with('error', 'Midtrans Error: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────
    private function processStripe($variant, $originalUnitPriceUSD, $discountUSD, $finalTotalUSD, $quantity, $currency, $email, $promoCode, $rawPromo)
    {
        \Log::info("--- ENTERING PROCESS STRIPE ---");

        // 1. Cek IP (Anti VPN/Proxy/RDP)
        $ip = request()->ip();
        if ($ip !== '127.0.0.1' && $ip !== '::1') {
            try {
                $response = Http::timeout(5)->get("http://proxycheck.io/v2/{$ip}?vpn=1&asn=1");
                if ($response->successful()) {
                    $data = $response->json();
                    if (isset($data[$ip]['proxy']) && $data[$ip]['proxy'] === 'yes') {
                        \Log::warning("VPN/Proxy detected for Stripe checkout. IP: {$ip}");
                        return back()->with('error', 'Keamanan: VPN atau Proxy terdeteksi. Transaksi ditolak untuk mencegah fraud. Silakan matikan VPN Anda.');
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Proxycheck API error: ' . $e->getMessage());
            }
        }

        try {
            Stripe::setApiKey(env('STRIPE_SECRET'));

            $orderId = 'INV-' . time() . '-' . rand(100, 999);

            // Stripe line_items doesn't easily allow negative items without coupons.
            // So we divide the final total by quantity to get the new unit amount.
            $unitAmountCents = (int) round(($finalTotalUSD / $quantity) * 100);
            $totalCents = $unitAmountCents * $quantity;

            $session = StripeSession::create([
                'payment_method_types' => ['card'],
                'payment_method_options' => [
                    'card' => [
                        'request_three_d_secure' => 'any',
                    ],
                ],
                'billing_address_collection' => 'required',
                'line_items'           => [[
                    'price_data' => [
                        'currency'     => strtolower($currency),
                        'product_data' => [
                            'name' => $variant->product->name . ' (' . $variant->duration . ')',
                        ],
                        'unit_amount'  => $unitAmountCents,
                    ],
                    'quantity' => $quantity,
                ]],
                'mode'           => 'payment',
                'customer_email' => $email,
                'success_url'    => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url'     => url('/'),  // cancel: kembali ke home
            ]);

            // ── Simpan transaksi ke database ──
            Transaction::create([
                'user_id'        => auth()->id(),
                'reference'      => $orderId,
                'merchant_ref'   => $orderId,
                'product_name'   => $variant->product->name . ' (' . $variant->duration . ')',
                'variant_id'     => $variant->id,
                'quantity'       => $quantity,
                'price'          => $totalCents,  // simpan dalam cents USD
                'original_price' => (int) round($originalUnitPriceUSD * $quantity * 100),
                'promo_code'     => $promoCode,
                'gift_username'  => $variant->product->delivery_method === 'gift' ? request('gift_username') : null,
                'customer_email' => $email,
                'status'         => 'UNPAID',
                'payment_method' => 'STRIPE',
                'checkout_url'   => $session->url,
            ]);

            self::sendAdminChannelNotification([
                'order_id'       => $orderId,
                'product_name'   => $variant->product->name . ' (' . $variant->duration . ')',
                'quantity'       => $quantity,
                'total'          => $totalCents / 100,
                'currency'       => strtoupper($currency),
                'payment_method' => 'Stripe',
                'customer_email' => $email,
                'discord_name'   => auth()->user()->name ?? null,
                'discord_id'     => auth()->user()->discord_id ?? null,
            ]);

            // Simpan data ke session untuk halaman sukses
            $this->storeOrderSession([
                'order_id'       => $orderId,
                'variant_id'     => $variant->id,
                'product_name'   => $variant->product->name . ' (' . $variant->duration . ')',
                'quantity'       => $quantity,
                'unit_price'     => $originalUnitPriceUSD,
                'discount'       => $discountUSD,
                'total'          => $totalCents / 100,
                'currency'       => strtoupper($currency),
                'payment_method' => 'Stripe (Credit/Debit Card)',
                'customer_email' => $email,
                'promo_code'     => $promoCode,
                'paid_at'        => now()->format('d M Y, H:i'),
            ]);

            return redirect($session->url);

        } catch (\Exception $e) {
            \Log::error('Stripe checkout error: ' . $e->getMessage(), [
                'variant_id' => $variant->id ?? null,
                'email'      => $email ?? null,
                'unitPrice'  => $originalUnitPriceUSD ?? null,
                'quantity'   => $quantity ?? null,
            ]);
            return back()->with('error', 'Stripe Error: ' . $e->getMessage());
        }
    }

    // ─────────────────────────────────────────────────────
    private function processPaypal($variant, $originalUnitPriceUSD, $discountUSD, $finalTotalUSD, $quantity, $currency, $email, $promoCode)
    {
        $clientId     = env('PAYPAL_CLIENT_ID');
        $clientSecret = env('PAYPAL_CLIENT_SECRET');
        $baseUrl      = env('PAYPAL_MODE', 'sandbox') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        if (!$clientId || !$clientSecret) {
            return back()->with('error', 'PayPal is not configured. Please use another payment method.');
        }

        // Get access token
        $tokenRes = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if (!$tokenRes->successful()) {
            \Log::error('PayPal token error', ['response' => $tokenRes->json()]);
            return back()->with('error', 'PayPal connection failed. Please try another payment method.');
        }

        $accessToken = $tokenRes->json('access_token');
        $orderId     = 'INV-' . time() . '-' . rand(100, 999);

        $orderRes = Http::withToken($accessToken)
            ->post("{$baseUrl}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [[
                    'reference_id' => $orderId,
                    'amount'       => [
                        'currency_code' => 'USD',
                        'value'         => number_format($finalTotalUSD, 2, '.', ''),
                    ],
                    'description' => substr($variant->product->name . ' (' . $variant->duration . ')', 0, 127),
                ]],
                'application_context' => [
                    'return_url'          => route('payment.paypal.return'),
                    'cancel_url'          => url('/'),
                    'brand_name'          => 'ABUSER STORE',
                    'user_action'         => 'PAY_NOW',
                    'shipping_preference' => 'NO_SHIPPING',
                ],
            ]);

        if (!$orderRes->successful()) {
            \Log::error('PayPal order creation failed', ['response' => $orderRes->json()]);
            return back()->with('error', 'PayPal order creation failed. Please try again.');
        }

        $paypalData = $orderRes->json();
        $paypalId   = $paypalData['id'];
        $approveUrl = collect($paypalData['links'])->firstWhere('rel', 'approve')['href'] ?? null;

        if (!$approveUrl) {
            return back()->with('error', 'PayPal approval URL not found.');
        }

        $totalCents = (int) round($finalTotalUSD * 100);

        Transaction::create([
            'user_id'        => auth()->id(),
            'reference'      => $orderId,
            'merchant_ref'   => $paypalId,
            'product_name'   => $variant->product->name . ' (' . $variant->duration . ')',
            'variant_id'     => $variant->id,
            'quantity'       => $quantity,
            'price'          => $totalCents,
            'original_price' => (int) round($originalUnitPriceUSD * $quantity * 100),
            'promo_code'     => $promoCode,
            'gift_username'  => $variant->product->delivery_method === 'gift' ? request('gift_username') : null,
            'customer_email' => $email,
            'status'         => 'UNPAID',
            'payment_method' => 'PAYPAL',
            'checkout_url'   => $approveUrl,
        ]);

        $this->storeOrderSession([
            'order_id'       => $orderId,
            'variant_id'     => $variant->id,
            'product_name'   => $variant->product->name . ' (' . $variant->duration . ')',
            'quantity'       => $quantity,
            'unit_price'     => $originalUnitPriceUSD,
            'discount'       => $discountUSD,
            'total'          => $totalCents / 100,
            'currency'       => 'USD',
            'payment_method' => 'PayPal (Goods & Services)',
            'customer_email' => $email,
            'promo_code'     => $promoCode,
            'paid_at'        => now()->format('d M Y, H:i'),
        ]);

        self::sendAdminChannelNotification([
            'order_id'       => $orderId,
            'product_name'   => $variant->product->name . ' (' . $variant->duration . ')',
            'quantity'       => $quantity,
            'total'          => $totalCents / 100,
            'currency'       => 'USD',
            'payment_method' => 'PayPal',
            'customer_email' => $email,
            'discord_name'   => auth()->user()->name ?? null,
            'discord_id'     => auth()->user()->discord_id ?? null,
        ]);

        return redirect($approveUrl);
    }

    // ─────────────────────────────────────────────────────
    public function paypalReturn(Request $request)
    {
        $paypalOrderId = $request->token;

        if (!$paypalOrderId) {
            return redirect('/')->with('error', 'PayPal payment was cancelled.');
        }

        $clientId     = env('PAYPAL_CLIENT_ID');
        $clientSecret = env('PAYPAL_CLIENT_SECRET');
        $baseUrl      = env('PAYPAL_MODE', 'sandbox') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';

        // Get access token
        $tokenRes = Http::withBasicAuth($clientId, $clientSecret)
            ->asForm()
            ->post("{$baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if (!$tokenRes->successful()) {
            return redirect('/')->with('error', 'PayPal verification failed.');
        }

        $accessToken = $tokenRes->json('access_token');

        // Capture the order
        $captureRes = Http::withToken($accessToken)
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post("{$baseUrl}/v2/checkout/orders/{$paypalOrderId}/capture", (object)[]);

        if (!$captureRes->successful()) {
            \Log::error('PayPal capture failed', ['response' => $captureRes->json(), 'paypal_order_id' => $paypalOrderId]);
            return redirect('/')->with('error', 'PayPal payment capture failed. Contact support with order ID: ' . $paypalOrderId);
        }

        $captureData = $captureRes->json();

        if (($captureData['status'] ?? '') !== 'COMPLETED') {
            return redirect('/')->with('error', 'PayPal payment not completed.');
        }

        // Find transaction by merchant_ref (PayPal order ID)
        $trx = Transaction::where('merchant_ref', $paypalOrderId)->first();

        if (!$trx) {
            \Log::error('PayPal return: transaction not found', ['paypal_order_id' => $paypalOrderId]);
            return redirect('/')->with('error', 'Transaction not found.');
        }

        $issuedCodes = [];

        if ($trx->status !== 'PAID') {
            \Illuminate\Support\Facades\DB::transaction(function () use ($trx, &$issuedCodes, $captureData) {
                $trx->update([
                    'status'          => 'PAID',
                    'payment_details' => json_encode([
                        'type'      => 'paypal',
                        'paypal_id' => $trx->merchant_ref,
                    ]),
                ]);

                if (empty($trx->vouchers_issued)) {
                    $vouchers = \App\Models\VoucherCode::where('variant_id', $trx->variant_id)
                        ->where('status', 'AVAILABLE')
                        ->lockForUpdate()
                        ->take($trx->quantity)
                        ->get();

                    if ($vouchers->count() >= $trx->quantity) {
                        foreach ($vouchers as $vc) {
                            $vc->update(['status' => 'SOLD']);
                            $issuedCodes[] = $vc->code;
                        }
                        if (!empty($issuedCodes)) {
                            $trx->update(['vouchers_issued' => implode(', ', $issuedCodes)]);
                        }
                    } else {
                        \Log::critical('PAYPAL OVERSOLD PREVENTED: Order ' . $trx->reference);
                        $trx->update(['status' => 'FAILED_OVERSOLD']);
                    }
                } else {
                    $issuedCodes = explode(', ', $trx->vouchers_issued);
                }
            });

            // Send Discord DM + admin notification
            $user = \App\Models\User::find($trx->user_id);
            $orderData = [
                'order_id'       => $trx->reference,
                'product_name'   => $trx->product_name,
                'quantity'       => $trx->quantity,
                'unit_price'     => ($trx->original_price / max($trx->quantity, 1)) / 100,
                'discount'       => 0,
                'total'          => $trx->price / 100,
                'currency'       => 'USD',
                'payment_method' => 'PayPal',
                'customer_email' => $trx->customer_email,
                'promo_code'     => $trx->promo_code,
                'paid_at'        => now()->format('d M Y, H:i'),
                'discord_name'   => $user->name ?? null,
                'discord_id'     => $user->discord_id ?? null,
                'vouchers'       => $issuedCodes,
            ];
            self::sendDiscordNotification($orderData);
        } else {
            $issuedCodes = explode(', ', $trx->vouchers_issued ?? '');
        }

        $trx = $trx->fresh();
        $user = auth()->user();

        $this->storeOrderSession([
            'order_id'       => $trx->reference,
            'variant_id'     => $trx->variant_id,
            'product_name'   => $trx->product_name,
            'quantity'       => $trx->quantity,
            'unit_price'     => ($trx->original_price / max($trx->quantity, 1)) / 100,
            'discount'       => 0,
            'total'          => $trx->price / 100,
            'currency'       => 'USD',
            'payment_method' => 'PayPal (Goods & Services)',
            'customer_email' => $trx->customer_email,
            'promo_code'     => $trx->promo_code,
            'paid_at'        => $trx->updated_at->format('d M Y, H:i'),
            'discord_name'   => $user->name ?? null,
            'discord_id'     => $user->discord_id ?? null,
            'vouchers'       => $issuedCodes,
        ]);

        return redirect(route('payment.success'));
    }

    // ─────────────────────────────────────────────────────
    private function processCrypto($variant, $originalUnitPriceUSD, $discountUSD, $finalTotalUSD, $quantity, $email, $promoCode)
    {
        $apiKey = env('NOWPAYMENTS_API_KEY');

        if (!$apiKey) {
            return back()->with('error', 'Crypto payments are currently unavailable. Please use another payment method.');
        }

        $orderId = 'INV-' . time() . '-' . rand(100, 999);

        try {
            $response = Http::withHeaders([
                'x-api-key'    => $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.nowpayments.io/v1/invoice', [
                'price_amount'        => round($finalTotalUSD, 2),
                'price_currency'      => 'usd',
                'order_id'            => $orderId,
                'order_description'   => substr($variant->product->name . ' (' . $variant->duration . ')', 0, 100),
                'ipn_callback_url'    => route('callback.crypto'),
                'success_url'         => route('payment.success') . '?ref=' . $orderId,
                'cancel_url'          => url('/'),
                'is_fixed_rate'       => false,
                'is_fee_paid_by_user' => true,
            ]);

            if (!$response->successful()) {
                \Log::error('NOWPayments invoice failed', ['response' => $response->json()]);
                return back()->with('error', 'Crypto payment setup failed. Please try again.');
            }

            $invoice    = $response->json();
            $invoiceUrl = $invoice['invoice_url'] ?? null;
            $invoiceId  = (string) ($invoice['id'] ?? $orderId);

            if (!$invoiceUrl) {
                return back()->with('error', 'Crypto payment URL not received.');
            }

            $totalCents = (int) round($finalTotalUSD * 100);

            Transaction::create([
                'user_id'        => auth()->id(),
                'reference'      => $orderId,
                'merchant_ref'   => $invoiceId,
                'product_name'   => $variant->product->name . ' (' . $variant->duration . ')',
                'variant_id'     => $variant->id,
                'quantity'       => $quantity,
                'price'          => $totalCents,
                'original_price' => (int) round($originalUnitPriceUSD * $quantity * 100),
                'promo_code'     => $promoCode,
                'gift_username'  => $variant->product->delivery_method === 'gift' ? request('gift_username') : null,
                'customer_email' => $email,
                'status'         => 'UNPAID',
                'payment_method' => 'CRYPTO',
                'checkout_url'   => $invoiceUrl,
            ]);

            $this->storeOrderSession([
                'order_id'       => $orderId,
                'variant_id'     => $variant->id,
                'product_name'   => $variant->product->name . ' (' . $variant->duration . ')',
                'quantity'       => $quantity,
                'unit_price'     => $originalUnitPriceUSD,
                'discount'       => $discountUSD,
                'total'          => $totalCents / 100,
                'currency'       => 'USD',
                'payment_method' => 'Crypto (BTC/ETH/USDT/+300 coins)',
                'customer_email' => $email,
                'promo_code'     => $promoCode,
                'paid_at'        => now()->format('d M Y, H:i'),
            ]);

            self::sendAdminChannelNotification([
                'order_id'       => $orderId,
                'product_name'   => $variant->product->name . ' (' . $variant->duration . ')',
                'quantity'       => $quantity,
                'total'          => $totalCents / 100,
                'currency'       => 'USD',
                'payment_method' => 'Crypto',
                'customer_email' => $email,
                'discord_name'   => auth()->user()->name ?? null,
                'discord_id'     => auth()->user()->discord_id ?? null,
            ]);

            return redirect($invoiceUrl);

        } catch (\Exception $e) {
            \Log::error('Crypto payment exception: ' . $e->getMessage());
            return back()->with('error', 'Crypto payment error. Please try again.');
        }
    }

    // ─────────────────────────────────────────────────────
    private function storeOrderSession(array $data)
    {
        session(['payment_success_order' => $data]);
        session()->save();
    }

    // ─────────────────────────────────────────────────────
    public function success()
    {
        // Ambil data order dari session
        $order = session('payment_success_order');

        // Fallback: if arriving from crypto success URL with ?ref= but no session
        if (!$order && request()->filled('ref')) {
            $refTrx = \App\Models\Transaction::where('reference', request('ref'))->first();
            if ($refTrx && strtoupper($refTrx->payment_method) === 'CRYPTO') {
                $user = auth()->user();
                $order = [
                    'order_id'        => $refTrx->reference,
                    'product_name'    => $refTrx->product_name,
                    'quantity'        => $refTrx->quantity,
                    'unit_price'      => ($refTrx->original_price / max($refTrx->quantity, 1)) / 100,
                    'discount'        => 0,
                    'total'           => $refTrx->price / 100,
                    'currency'        => 'USD',
                    'payment_method'  => 'Crypto (BTC/ETH/USDT/+300 coins)',
                    'customer_email'  => $refTrx->customer_email,
                    'promo_code'      => $refTrx->promo_code,
                    'paid_at'         => now()->format('d M Y, H:i'),
                    'discord_name'    => $user->name ?? null,
                    'discord_id'      => $user->discord_id ?? null,
                    'crypto_pending'  => $refTrx->status !== 'PAID',
                    'crypto_order_id' => $refTrx->reference,
                ];
                if ($refTrx->status === 'PAID' && !empty($refTrx->vouchers_issued)) {
                    $order['vouchers'] = explode(', ', $refTrx->vouchers_issued);
                    $order['crypto_pending'] = false;
                }
            }
        }

        // Jika tidak ada data di session (direct access), buat placeholder
        if (!$order) {
            $order = [
                'order_id'       => 'N/A',
                'product_name'   => 'Produk ABUSER STORE',
                'quantity'       => 1,
                'unit_price'     => 0,
                'discount'       => 0,
                'total'          => 0,
                'currency'       => 'USD',
                'payment_method' => '-',
                'customer_email' => auth()->user()?->email ?? '-',
                'promo_code'     => null,
                'paid_at'        => now()->format('d M Y, H:i'),
            ];
        }

        // Tambahkan info Discord jika login
        if (auth()->check()) {
            $order['discord_name'] = auth()->user()->name ?? null;
            $order['discord_id']   = auth()->user()->discord_id ?? null;
        }

        // Cek database untuk potong stok jika belum diproses webhook (penting untuk localhost)
        $issuedCodes = [];
        $shouldSendDM = false;

        if (isset($order['order_id'])) {
            $trx = \App\Models\Transaction::where('reference', $order['order_id'])->first();

            // For CRYPTO: check DB status — webhook may not have fired yet
            if ($trx && strtoupper($trx->payment_method ?? '') === 'CRYPTO') {
                if ($trx->status === 'PAID' && !empty($trx->vouchers_issued)) {
                    $issuedCodes = explode(', ', $trx->vouchers_issued);
                } else {
                    $order['crypto_pending'] = true;
                    $order['crypto_order_id'] = $trx->reference ?? null;
                }
                session()->forget('payment_success_order');
                $order['vouchers'] = $issuedCodes;
                return view('payment.success', compact('order'));
            }

            if ($trx) {
                // Verifikasi status pembayaran Stripe
                if (strtoupper($trx->payment_method) === 'STRIPE') {
                    if (!request()->has('session_id')) {
                        return redirect('/')->with('error', 'Sesi pembayaran Stripe tidak valid.');
                    }
                    try {
                        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET'));
                        $sessionStripe = \Stripe\Checkout\Session::retrieve([
                            'id' => request('session_id'),
                            'expand' => ['payment_intent.payment_method']
                        ]);

                        // PROTEKSI UTAMA: Pastikan benar-benar sudah dibayar
                        if ($sessionStripe->payment_status !== 'paid') {
                            return redirect('/')->with('error', 'Pembayaran Stripe belum diselesaikan atau gagal.');
                        }

                        if ($sessionStripe && $sessionStripe->payment_intent && $sessionStripe->payment_intent->payment_method) {
                            $pm = $sessionStripe->payment_intent->payment_method;
                            if ($pm->type === 'card' && $pm->card) {
                                $paymentDetails = [
                                    'brand' => ucfirst($pm->card->brand),
                                    'last4' => $pm->card->last4,
                                ];
                                $trx->payment_details = json_encode($paymentDetails);
                                $trx->save();
                            }
                        }
                    } catch (\Exception $e) {
                        \Log::error('Failed to retrieve Stripe session details: ' . $e->getMessage());
                        return redirect('/')->with('error', 'Terjadi kesalahan saat memverifikasi pembayaran Stripe.');
                    }
                }

                if (!empty($trx->vouchers_issued)) {
                    // Sudah diproses oleh Webhook Callback
                    $issuedCodes = explode(", ", $trx->vouchers_issued);
                } else {
                    // Belum diproses, potong stok sekarang dengan DB Transaction & Pessimistic Locking
                    \Illuminate\Support\Facades\DB::transaction(function() use ($trx, &$issuedCodes, &$shouldSendDM) {
                        $vouchers = \App\Models\VoucherCode::where('variant_id', $trx->variant_id)
                                                           ->where('status', 'AVAILABLE')
                                                           ->lockForUpdate() // Prevent Race Condition & Overselling
                                                           ->take($trx->quantity)
                                                           ->get();

                        if ($vouchers->count() >= $trx->quantity) {
                            foreach ($vouchers as $vc) {
                                $vc->update(['status' => 'SOLD']);
                                $issuedCodes[] = $vc->code;
                            }

                            $trx->update([
                                'status' => 'PAID',
                                'vouchers_issued' => implode(", ", $issuedCodes)
                            ]);
                            $shouldSendDM = true;
                        } else {
                            // Mencegah overselling jika stok habis saat pembayaran berhasil
                            \Log::error('Race condition prevented! Not enough vouchers available for Order: ' . $trx->reference);
                            $trx->update(['status' => 'FAILED_OVERSOLD']);
                        }
                    });
                }
            }
        }
        $order['vouchers'] = $issuedCodes;

        // === KIRIM DISCORD DM ===
        // Pastikan discord_id selalu diambil dari DB user yang login (bukan hanya session)
        $discordId = null;
        if (auth()->check()) {
            $discordId = auth()->user()->discord_id ?? null;
            $order['discord_id']   = $discordId;
            $order['discord_name'] = auth()->user()->name ?? null;
        }

        // Fallback: ambil dari transaksi jika auth sudah expired (jarang terjadi)
        if (!$discordId && isset($trx)) {
            $trxUser = \App\Models\User::find($trx->user_id);
            $discordId = $trxUser->discord_id ?? null;
            $order['discord_id']   = $discordId;
            $order['discord_name'] = $trxUser->name ?? null;
        }

        \Log::info('Discord DM attempt', [
            'discord_id' => $discordId,
            'order_id'   => $order['order_id'] ?? 'N/A',
            'vouchers'   => $issuedCodes,
        ]);

        if ($discordId) {
            $embed = self::buildInvoiceEmbed($order);
            self::sendDiscordDM($discordId, $embed);
        } else {
            \Log::warning('Discord DM skipped: no discord_id', [
                'user_id'  => auth()->id(),
                'order_id' => $order['order_id'] ?? 'N/A',
            ]);
        }

        // Hapus session agar tidak bisa diakses ulang
        session()->forget('payment_success_order');

        return view('payment.success', compact('order'));
    }

    // ─────────────────────────────────────────────────────
    public static function sendDiscordNotification(array $order)
    {
        $embed = self::buildInvoiceEmbed($order);

        // 1. DM langsung ke user via Discord Bot
        self::sendDiscordDM($order['discord_id'] ?? null, $embed);
    }

    /**
     * Send a notification to admin Discord channel webhook when a new order is placed.
     */
    public static function sendAdminChannelNotification(array $order): void
    {
        $webhookUrl = env('DISCORD_ADMIN_WEBHOOK');
        if (!$webhookUrl) return;

        $currency = $order['currency'] ?? 'IDR';
        $amount   = $currency === 'IDR'
            ? 'Rp ' . number_format($order['total'] ?? 0, 0, ',', '.')
            : '$ ' . number_format($order['total'] ?? 0, 2);

        $payload = [
            'embeds' => [[
                'title'       => '🛒 New Order — ABUSER STORE',
                'description' => 'A new order has been placed!',
                'color'       => 0x22C55E,
                'fields'      => [
                    ['name' => '📋 Invoice',    'value' => '`' . ($order['order_id'] ?? 'N/A') . '`',     'inline' => true],
                    ['name' => '📦 Product',    'value' => $order['product_name'] ?? '-',                  'inline' => true],
                    ['name' => '💳 Payment',    'value' => $order['payment_method'] ?? '-',               'inline' => true],
                    ['name' => '💰 Amount',     'value' => '**' . $amount . '**',                          'inline' => true],
                    ['name' => '📧 Email',      'value' => $order['customer_email'] ?? '-',               'inline' => true],
                    ['name' => '👤 Discord',    'value' => $order['discord_name'] ?? 'Unknown',           'inline' => true],
                ],
                'timestamp' => now()->toIso8601String(),
                'footer'    => ['text' => 'ABUSER STORE · New Order Notification'],
            ]],
        ];

        try {
            \Illuminate\Support\Facades\Http::timeout(5)->post($webhookUrl, $payload);
        } catch (\Exception $e) {
            \Log::warning('Admin Discord webhook notification failed: ' . $e->getMessage());
        }
    }

    // ── Buat embed invoice ─────────────────────────────
    public static function buildInvoiceEmbed(array $order): array
    {
        $currency    = $order['currency'] ?? 'USD';
        $formatPrice = fn($n) => $currency === 'IDR'
            ? 'Rp ' . number_format($n, 0, ',', '.')
            : '$ ' . number_format($n, 2);

        $discountLine = '';
        if (!empty($order['discount']) && $order['discount'] > 0) {
            $discountLine = "\n🎟 **Diskon** (`{$order['promo_code']}`): -" . $formatPrice($order['discount']);
        }

        $name = $order['discord_name'] ?? 'Pelanggan';

        $embed = [
            'title'       => '🧾 Invoice Pembayaran – ABUSER STORE',
            'description' => "Halo **{$name}**! Pembayaran kamu sudah dikonfirmasi. Berikut detail invoice kamu:",
            'color'       => 0x00C6FF,
            'fields'      => [
                [
                    'name'   => '📋 Invoice No.',
                    'value'  => '`' . ($order['order_id'] ?? 'N/A') . '`',
                    'inline' => true,
                ],
                [
                    'name'   => '📅 Tanggal',
                    'value'  => ($order['paid_at'] ?? now()->format('d M Y, H:i')) . ' WIB',
                    'inline' => true,
                ],
                [
                    'name'   => "\u200b",
                    'value'  => "\u200b",
                    'inline' => false,
                ],
                [
                    'name'   => '📦 Produk',
                    'value'  => $order['product_name'] ?? '-',
                    'inline' => true,
                ],
                [
                    'name'   => '🔢 Jumlah',
                    'value'  => 'x' . ($order['quantity'] ?? 1),
                    'inline' => true,
                ],
                [
                    'name'   => '💲 Harga Satuan',
                    'value'  => $formatPrice($order['unit_price'] ?? 0),
                    'inline' => true,
                ],
                [
                    'name'   => '💳 Metode Bayar',
                    'value'  => $order['payment_method'] ?? '-',
                    'inline' => true,
                ],
                [
                    'name'   => '📧 Email',
                    'value'  => $order['customer_email'] ?? '-',
                    'inline' => true,
                ],
                [
                    'name'   => "\u200b",
                    'value'  => "\u200b",
                    'inline' => false,
                ],
                [
                    'name'   => '💰 TOTAL DIBAYAR',
                    'value'  => '**' . $formatPrice($order['total'] ?? 0) . '**' . $discountLine,
                    'inline' => false,
                ],
            ],
        ];

        if (!empty($order['vouchers'])) {
            $voucherText = implode("\n", array_map(fn($c) => "`{$c}`", $order['vouchers']));
            $embed['fields'][] = [
                'name'   => '🔑 Voucher Codes',
                'value'  => $voucherText,
                'inline' => false,
            ];
        }

        $embed['footer'] = [
            'text' => 'ABUSER STORE · Terima kasih sudah berbelanja! 🎉',
        ];
        $embed['timestamp'] = now()->toIso8601String();

        return $embed;
    }

    // ── Kirim DM ke user via Discord Bot ──────────────
    public static function sendDiscordDM(?string $discordId, array $embed): void
    {
        $botToken = env('DISCORD_BOT_TOKEN');
        if (!$botToken || !$discordId) return;

        try {
            $headers = [
                'Authorization' => 'Bot ' . $botToken,
                'Content-Type'  => 'application/json',
            ];

            // Step 1: Buat DM channel dengan user (dengan retry otomatis)
            $dmRes = Http::timeout(5)
                ->withHeaders($headers)
                ->retry(3, 1000) // Retry 3 kali, delay 1 detik (1000ms)
                ->post('https://discord.com/api/v10/users/@me/channels', [
                    'recipient_id' => $discordId,
                ]);

            if (!$dmRes->successful()) {
                \Log::critical('Discord DM channel creation FAILED permanently after 3 retries.', ['response' => $dmRes->body(), 'discord_id' => $discordId]);
                return;
            }

            $channelId = $dmRes->json('id');

            // Step 2: Kirim pesan embed ke DM channel (dengan retry otomatis)
            $msgRes = Http::timeout(5)
                ->withHeaders($headers)
                ->retry(3, 1000) // Retry 3 kali, delay 1 detik
                ->post("https://discord.com/api/v10/channels/{$channelId}/messages", [
                    'content' => '> 🛒 **ABUSER STORE** – Invoice Pembayaran Kamu',
                    'embeds'  => [$embed],
                ]);

            if (!$msgRes->successful()) {
                \Log::critical('Discord message sending FAILED permanently after 3 retries.', ['response' => $msgRes->body(), 'channel_id' => $channelId]);
            }

        } catch (\Exception $e) {
            \Log::critical('Discord DM FATAL ERROR: ' . $e->getMessage(), ['discord_id' => $discordId]);
        }
    }

    // Webhook dihilangkan sesuai permintaan user

    // ─────────────────────────────────────────────────────
    public function checkPromo(Request $request)
    {
        $lang = $request->input('lang', 'en');
        $promo = Promo::where('code', $request->promo_code)->first();
        if (!$promo) {
            $msg = $lang === 'id' ? 'Kode Tidak Ditemukan' : 'Code Not Found';
            return response()->json(['success' => false, 'message' => $msg]);
        }

        // Check if active and valid
        $isActive = ($promo->is_active == 1 || is_null($promo->is_active));
        $isValidDate = true;
        if (!is_null($promo->valid_until)) {
            $expiredDate = \Carbon\Carbon::parse($promo->valid_until)->endOfDay();
            if ($expiredDate->isPast() && !$expiredDate->isToday()) $isValidDate = false;
        }

        if (!$isActive || !$isValidDate) {
            $msg = $lang === 'id' ? 'Kode promo tidak aktif atau kedaluwarsa' : 'Promo code is inactive or expired';
            return response()->json(['success' => false, 'message' => $msg]);
        }

        // Validate Specific Product
        $variant = Variant::find($request->variant_id);
        if ($promo->product_id && $variant && $promo->product_id != $variant->product_id) {
            $msg = $lang === 'id' ? 'Kode promo tidak berlaku untuk produk ini' : 'Promo code is not applicable for this product';
            return response()->json(['success' => false, 'message' => $msg]);
        }

        // Validate Min Qty
        $quantity = (int) $request->input('quantity', 1);
        if ($quantity < $promo->min_qty) {
            $msg = $lang === 'id' ? 'Minimal pembelian ' . $promo->min_qty . ' produk' : 'Minimum purchase is ' . $promo->min_qty . ' product(s)';
            return response()->json(['success' => false, 'message' => $msg]);
        }

        if (auth()->check()) {
            if ($promo->usage_limit_per_user > 0) {
                $usedCount = Transaction::where('user_id', auth()->id())
                    ->where('promo_code', $promo->code)
                    ->whereIn('status', ['PAID', 'UNPAID'])
                    ->count();
                if ($usedCount >= $promo->usage_limit_per_user) {
                    $msg = $lang === 'id' ? 'Batas penggunaan maksimum tercapai' : 'Maximum usage limit reached';
                    return response()->json(['success' => false, 'message' => $msg]);
                }
            } else {
                // Backward compatibility just in case limit is 0 but we want some hardcoded block, but usually 0 means unlimited.
                // Original logic prevented any reuse if no limit logic existed, but we changed it to check usage_limit_per_user.
            }
        }

        $successMsg = $lang === 'id' ? 'Kode Berhasil!' : 'Code Applied!';
        return response()->json([
            'success' => true,
            'message' => $successMsg,
            'type'    => $promo->type,
            'value'   => (float) $promo->value,
            'max_discount' => (float) $promo->max_discount,
        ]);
    }
}
