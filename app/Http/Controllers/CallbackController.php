<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;

class CallbackController extends Controller
{
    public function handle(Request $request)
    {
        \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION');
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        try {
            $notif = new \Midtrans\Notification();

            // 0. SIGNATURE VALIDATION (Mencegah Bypass Pembayaran)
            $serverKey = env('MIDTRANS_SERVER_KEY');
            $expectedSignature = hash('sha512', $notif->order_id . $notif->status_code . $notif->gross_amount . $serverKey);
            if ($expectedSignature !== $notif->signature_key) {
                \Log::critical('MIDTRANS SIGNATURE MISMATCH (POTENSI FRAUD)', ['order_id' => $notif->order_id]);
                return response()->json(['message' => 'Invalid Signature'], 403);
            }

            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $order_id = $notif->order_id;
            $fraud = $notif->fraud_status;

            $trx = Transaction::where('reference', $order_id)->first();

            if ($trx) {
                // 1. PAYMENT FRAUD VALIDATION (Amount Mismatch)
                $paidAmount = (float) $notif->gross_amount;
                $expectedAmount = (float) $trx->price;

                if (abs($paidAmount - $expectedAmount) > 1) { // Toleransi 1 rupiah/sen
                    \Log::critical('PAYMENT FRAUD ATTEMPT: Amount mismatch in webhook.', [
                        'order_id' => $order_id,
                        'paid_amount' => $paidAmount,
                        'expected_amount' => $expectedAmount
                    ]);
                    $trx->update(['status' => 'FRAUD']);
                    return response()->json(['message' => 'Invalid Amount'], 400);
                }

                if ($transaction == 'settlement' || ($transaction == 'capture' && $fraud != 'challenge')) {
                    if ($trx->status !== 'PAID') {
                        $paymentDetails = ['type' => $type];
                        if ($type == 'credit_card') {
                            $paymentDetails['brand'] = $notif->bank ?? 'Unknown';
                            $paymentDetails['last4'] = $notif->masked_card ? substr($notif->masked_card, -4) : null;
                        } elseif ($type == 'qris') {
                            $paymentDetails['issuer'] = $notif->issuer ?? $notif->acquirer ?? 'QRIS';
                        } elseif (isset($notif->bank)) {
                            $paymentDetails['bank'] = $notif->bank;
                        }

                        // 2. MENCEGAH RACE CONDITION DI WEBHOOK DENGAN DB LOCKING
                        $issuedCodes = [];

                        \Illuminate\Support\Facades\DB::transaction(function() use ($trx, &$issuedCodes, $paymentDetails) {
                            $trx->update([
                                'status' => 'PAID',
                                'payment_details' => json_encode($paymentDetails)
                            ]);

                            if (empty($trx->vouchers_issued)) {
                                $vouchers = \App\Models\VoucherCode::where('variant_id', $trx->variant_id)
                                                                   ->where('status', 'AVAILABLE')
                                                                   ->lockForUpdate() // Prevent Overselling
                                                                   ->take($trx->quantity)
                                                                   ->get();

                                if ($vouchers->count() >= $trx->quantity) {
                                    foreach ($vouchers as $vc) {
                                        $vc->update(['status' => 'SOLD']);
                                        $issuedCodes[] = $vc->code;
                                    }

                                    if (!empty($issuedCodes)) {
                                        $trx->update(['vouchers_issued' => implode(", ", $issuedCodes)]);
                                    }
                                } else {
                                    \Log::critical('WEBHOOK OVERSOLD PREVENTED: Order ' . $trx->reference);
                                    $trx->update(['status' => 'FAILED_OVERSOLD']);
                                }
                            }
                        });

                            // Send Discord Notification
                            $user = \App\Models\User::where('email', $trx->customer_email)->first();
                            $orderData = [
                                'order_id'       => $trx->merchant_ref,
                                'variant_id'     => $trx->variant_id,
                                'product_name'   => $trx->product_name,
                                'quantity'       => $trx->quantity,
                                'unit_price'     => $trx->original_price / max($trx->quantity, 1),
                                'discount'       => 0, // Since promo logic is handled earlier
                                'total'          => $trx->price,
                                'currency'       => 'IDR',
                                'payment_method' => $trx->payment_method,
                                'customer_email' => $trx->customer_email,
                                'promo_code'     => $trx->promo_code,
                                'paid_at'        => now()->format('d M Y, H:i'),
                                'discord_name'   => $user->name ?? null,
                                'discord_id'     => $user->discord_id ?? null,
                                'vouchers'       => $issuedCodes,
                            ];

                            \App\Http\Controllers\PaymentController::sendDiscordNotification($orderData);
                        }
                } else if ($transaction == 'pending') {
                    $trx->update(['status' => 'UNPAID']);
                } else if ($transaction == 'deny') {
                    $trx->update(['status' => 'FAILED']);
                } else if ($transaction == 'expire') {
                    $trx->update(['status' => 'EXPIRED']);
                } else if ($transaction == 'cancel') {
                    $trx->update(['status' => 'FAILED']);
                }
            }

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error'], 500);
        }

        return response()->json(['message' => 'OK']);
    }

    public function handleCrypto(Request $request)
    {
        $ipnSecret = env('NOWPAYMENTS_IPN_SECRET');

        // Verify IPN signature
        if ($ipnSecret) {
            $receivedSig = $request->header('x-nowpayments-sig');
            $sortedPayload = $request->all();
            ksort($sortedPayload, SORT_STRING);
            $expectedSig = hash_hmac('sha512', json_encode($sortedPayload), $ipnSecret);

            if ($receivedSig !== $expectedSig) {
                \Log::critical('NOWPAYMENTS IPN SIGNATURE MISMATCH', ['received' => $receivedSig]);
                return response()->json(['message' => 'Invalid signature'], 403);
            }
        }

        $data          = $request->all();
        $orderId       = $data['order_id'] ?? null;
        $paymentStatus = $data['payment_status'] ?? '';

        if (!$orderId) {
            return response()->json(['message' => 'No order ID'], 400);
        }

        $trx = Transaction::where('reference', $orderId)->first();

        if (!$trx) {
            \Log::error('CRYPTO CALLBACK: Transaction not found', ['order_id' => $orderId]);
            return response()->json(['message' => 'Not found'], 404);
        }

        if (in_array($paymentStatus, ['finished', 'confirmed'])) {
            if ($trx->status !== 'PAID') {
                $issuedCodes = [];

                \Illuminate\Support\Facades\DB::transaction(function () use ($trx, &$issuedCodes, $data) {
                    $trx->update([
                        'status'          => 'PAID',
                        'payment_details' => json_encode([
                            'type'          => 'crypto',
                            'pay_currency'  => $data['pay_currency'] ?? 'unknown',
                            'actually_paid' => $data['actually_paid'] ?? 0,
                            'payment_id'    => $data['payment_id'] ?? null,
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
                            \Log::critical('CRYPTO OVERSOLD PREVENTED: Order ' . $trx->reference);
                            $trx->update(['status' => 'FAILED_OVERSOLD']);
                        }
                    }
                });

                $user = \App\Models\User::find($trx->user_id);
                $orderData = [
                    'order_id'       => $trx->reference,
                    'product_name'   => $trx->product_name,
                    'quantity'       => $trx->quantity,
                    'unit_price'     => ($trx->original_price / max($trx->quantity, 1)) / 100,
                    'discount'       => 0,
                    'total'          => $trx->price / 100,
                    'currency'       => 'USD',
                    'payment_method' => 'Crypto',
                    'customer_email' => $trx->customer_email,
                    'promo_code'     => $trx->promo_code,
                    'paid_at'        => now()->format('d M Y, H:i'),
                    'discord_name'   => $user->name ?? null,
                    'discord_id'     => $user->discord_id ?? null,
                    'vouchers'       => $issuedCodes,
                ];
                \App\Http\Controllers\PaymentController::sendDiscordNotification($orderData);
            }
        } elseif (in_array($paymentStatus, ['failed', 'refunded'])) {
            $trx->update(['status' => 'FAILED']);
        } elseif ($paymentStatus === 'expired') {
            $trx->update(['status' => 'EXPIRED']);
        }

        return response()->json(['message' => 'OK']);
    }
}
