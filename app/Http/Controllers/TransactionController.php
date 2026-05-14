<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Promo;
use App\Models\Variant;
use Midtrans\Config;
use Midtrans\Snap;
use Carbon\Carbon;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('user');

        // === FILTER LOGIC ===
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function($q) use ($s) {
                $q->where('merchant_ref', 'like', "%$s%")
                  ->orWhere('product_name', 'like', "%$s%")
                  ->orWhere('customer_email', 'like', "%$s%")
                  ->orWhere('payment_method', 'like', "%$s%")
                  ->orWhere('status', 'like', "%$s%")
                  ->orWhere('vouchers_issued', 'like', "%$s%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'PENDING') {
                $query->where('status', 'UNPAID');
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('payment')) {
            $query->where('payment_method', $request->payment);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $sort = $request->input('sort', 'date_desc');
        switch ($sort) {
            case 'date_asc':
                $transactions = $query->orderBy('created_at', 'asc')->paginate(10)->appends($request->all());
                break;
            case 'price_desc':
                $transactions = $query->orderBy('price', 'desc')->paginate(10)->appends($request->all());
                break;
            case 'price_asc':
                $transactions = $query->orderBy('price', 'asc')->paginate(10)->appends($request->all());
                break;
            default: // date_desc
                $transactions = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->all());
                break;
        }

        // ── Summary stats ──
        $totalPaid    = Transaction::where('status', 'PAID')->count();
        $totalPending = Transaction::where('status', 'UNPAID')->count();
        $totalFailed  = Transaction::whereIn('status', ['FAILED', 'EXPIRED'])->count();

        // IDR revenue (Midtrans)
        $idrRevenue = Transaction::where('status', 'PAID')
            ->where('payment_method', '!=', 'STRIPE')
            ->sum('price');

        // USD revenue (Stripe – stored in cents, convert to dollars)
        $usdRevenueCents = Transaction::where('status', 'PAID')
            ->where('payment_method', 'STRIPE')
            ->sum('price');
        $usdRevenue = $usdRevenueCents / 100;

        // Avg order value per gateway
        $stripePaidCount   = Transaction::where('status','PAID')->where('payment_method','STRIPE')->count();
        $midtransPaidCount = Transaction::where('status','PAID')->where('payment_method','!=','STRIPE')->count();
        $avgUSD = $stripePaidCount   > 0 ? round($usdRevenue / $stripePaidCount, 2) : 0;
        $avgIDR = $midtransPaidCount > 0 ? round($idrRevenue / $midtransPaidCount) : 0;

        // Chart (empty defaults, loaded via AJAX)
        $chartLabels  = [];
        $chartDataIDR = [];
        $chartDataUSD = [];

        // ── Top 5 best-selling products ──
        $topProducts = Transaction::where('status', 'PAID')
            ->selectRaw('product_name, payment_method, COUNT(*) as total_orders, SUM(price) as total_revenue')
            ->groupBy('product_name', 'payment_method')
            ->orderByDesc('total_orders')
            ->limit(5)
            ->get();

        return view('admin.transactions', compact(
            'transactions',
            'chartLabels', 'chartDataIDR', 'chartDataUSD',
            'totalPaid', 'totalPending', 'totalFailed',
            'idrRevenue', 'usdRevenue',
            'avgIDR', 'avgUSD',
            'topProducts'
        ));
    }

    public function chartData(Request $request)
    {
        $period = $request->input('period', 'last30'); // last30, year, custom
        $year = $request->input('year', now()->year);
        $month = $request->input('month', now()->month);

        $labels = [];
        $dataIDR = [];
        $dataUSD = [];

        if ($period === 'last30') {
            $start = now()->subDays(29)->startOfDay();
            $end = now()->endOfDay();
            
            $rawIDR = Transaction::where('status', 'PAID')
                ->where('payment_method', '!=', 'STRIPE')
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw("DATE(created_at) as date, SUM(price) as total")
                ->groupBy('date')->pluck('total', 'date');

            $rawUSD = Transaction::where('status', 'PAID')
                ->where('payment_method', 'STRIPE')
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw("DATE(created_at) as date, SUM(price) as total")
                ->groupBy('date')->pluck('total', 'date');

            for ($i = 29; $i >= 0; $i--) {
                $dateObj = now()->subDays($i);
                $day = $dateObj->format('Y-m-d');
                $labels[] = $dateObj->format('d M');
                $dataIDR[] = (int) ($rawIDR[$day] ?? 0);
                $dataUSD[] = round(($rawUSD[$day] ?? 0) / 100, 2);
            }
        } elseif ($period === 'year') {
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();

            // Group by month
            $rawIDR = Transaction::where('status', 'PAID')
                ->where('payment_method', '!=', 'STRIPE')
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw("MONTH(created_at) as month, SUM(price) as total")
                ->groupBy('month')->pluck('total', 'month');

            $rawUSD = Transaction::where('status', 'PAID')
                ->where('payment_method', 'STRIPE')
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw("MONTH(created_at) as month, SUM(price) as total")
                ->groupBy('month')->pluck('total', 'month');

            $monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = $monthNames[$m - 1] . " $year";
                $dataIDR[] = (int) ($rawIDR[$m] ?? 0);
                $dataUSD[] = round(($rawUSD[$m] ?? 0) / 100, 2);
            }
        } elseif ($period === 'custom') {
            // Specific month of a specific year
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = Carbon::create($year, $month, 1)->endOfMonth();
            $daysInMonth = $start->daysInMonth;

            $rawIDR = Transaction::where('status', 'PAID')
                ->where('payment_method', '!=', 'STRIPE')
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw("DATE(created_at) as date, SUM(price) as total")
                ->groupBy('date')->pluck('total', 'date');

            $rawUSD = Transaction::where('status', 'PAID')
                ->where('payment_method', 'STRIPE')
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw("DATE(created_at) as date, SUM(price) as total")
                ->groupBy('date')->pluck('total', 'date');

            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dateObj = Carbon::create($year, $month, $d);
                $day = $dateObj->format('Y-m-d');
                $labels[] = $dateObj->format('d M');
                $dataIDR[] = (int) ($rawIDR[$day] ?? 0);
                $dataUSD[] = round(($rawUSD[$day] ?? 0) / 100, 2);
            }
        }

        return response()->json([
            'labels' => $labels,
            'dataIDR' => $dataIDR,
            'dataUSD' => $dataUSD,
        ]);
    }

    public function truncate()
    {
        \App\Models\Transaction::truncate();

        return redirect()->back()->with('success', 'Semua riwayat transaksi telah dibersihkan!');
    }

    public function process(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'variant_id' => 'required|exists:variants,id',
            'promo_code' => 'nullable|string'
        ]);

        $variant = Variant::with('product')->findOrFail($request->variant_id);
        
        $quantity = max(1, (int) $request->input('quantity', 1));
        $hargaAsliPerUnit = (int) $variant->price;
        $hargaAsliTotal = $hargaAsliPerUnit * $quantity;
        $hargaAkhir = $hargaAsliTotal;
        $potongan = 0;
        $kodePromo = null;

        // LOGIKA DISKON
        if ($request->filled('promo_code')) {
            $cleanCode = trim($request->promo_code);
            $promo = Promo::where('code', $cleanCode)->first();

            if ($promo) {
                // Cek Status & Tanggal
                $isActive = ($promo->is_active == 1 || is_null($promo->is_active)); 
                $isValidDate = true;
                
                if (!is_null($promo->valid_until)) {
                    $expiredDate = Carbon::parse($promo->valid_until)->endOfDay();
                    if ($expiredDate->isPast() && !$expiredDate->isToday()) {
                        $isValidDate = false;
                    }
                }

                if ($isActive && $isValidDate) {
                    $kodePromo = $promo->code;
                    $tipe = strtolower($promo->type);
                    $nilaiDiskon = $promo->value; 

                    if (str_contains($tipe, 'persen') || str_contains($tipe, 'percent')) {
                        $potongan = $hargaAsliTotal * ($nilaiDiskon / 100);
                    } else {
                        $potongan = $nilaiDiskon;
                    }

                    $hargaAkhir = $hargaAsliTotal - $potongan;
                    if ($hargaAkhir < 1) $hargaAkhir = 1;
                }
            }
        }

        // KONFIGURASI MIDTRANS
        Config::$serverKey = env('MIDTRANS_SERVER_KEY');
        Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
        Config::$isSanitized = true;
        Config::$is3ds = true;

        $orderId = 'INV-' . time() . '-' . rand(100, 999);

        $itemDetails = [
            [
                'id' => 'ITEM-' . $variant->id,
                'price' => $hargaAsliPerUnit,
                'quantity' => $quantity,
                'name' => substr($variant->product->name . ' - ' . $variant->duration, 0, 50)
            ]
        ];

        if ($potongan > 0) {
            $itemDetails[] = [
                'id' => 'DISC-' . $kodePromo,
                'price' => (int) -$potongan, 
                'quantity' => 1,
                'name' => 'Promo: ' . $kodePromo
            ];
        }

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $hargaAkhir,
            ],
            'item_details' => $itemDetails,
            'customer_details' => [
                'first_name' => 'Guest',
                'email' => $request->email,
            ],
        ];

        try {
            $paymentUrl = Snap::createTransaction($params)->redirect_url;

            Transaction::create([
                'user_id' => auth()->id(),
                'reference' => $orderId,
                'merchant_ref' => $orderId,
                'product_name' => $variant->product->name . ' - ' . $variant->duration,
                'variant_id' => $variant->id,
                'quantity' => $quantity,
                'price' => $hargaAkhir,
                'original_price' => $hargaAsliTotal,
                'customer_email' => $request->email ?? auth()->user()->email,
                'status' => 'UNPAID',
                'payment_method' => 'MIDTRANS',
                'checkout_url' => $paymentUrl
            ]);

            return redirect($paymentUrl);

        } catch (\Exception $e) {
            return back()->with('error', 'Error Midtrans: ' . $e->getMessage());
        }
    }

    // --- 2. API CEK PROMO ---
    public function checkPromo(Request $request)
    {
        $promo = Promo::where('code', $request->promo_code)->first();
        $variant = Variant::find($request->variant_id);

        if ($promo && $variant) {
            $isActive = ($promo->is_active == 1 || is_null($promo->is_active));
            $isValidDate = true;
            if (!is_null($promo->valid_until)) {
                $expiredDate = Carbon::parse($promo->valid_until)->endOfDay();
                if ($expiredDate->isPast() && !$expiredDate->isToday()) $isValidDate = false;
            }

            if ($isActive && $isValidDate) {
                $hargaAsli = $variant->price;
                $tipe = strtolower($promo->type);
                $nilaiDiskon = $promo->value;
                $potongan = 0;
                $pesan = "";

                if (str_contains($tipe, 'persen') || str_contains($tipe, 'percent')) {
                    $potongan = $hargaAsli * ($nilaiDiskon / 100);
                    $pesan = "Diskon " . $nilaiDiskon . "% diterapkan!";
                } else {
                    $potongan = $nilaiDiskon;
                    $pesan = "Potongan harga diterapkan!";
                }

                return response()->json([
                    'success' => true,
                    'message' => $pesan,
                    'discount_amount' => (int) $potongan
                ]);
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Kode tidak valid / kadaluarsa.',
            'discount_amount' => 0
        ]);
    }

    // --- 3. FUNGSI HAPUS (INI YANG DITAMBAHKAN UNTUK MEMPERBAIKI ERROR) ---
    public function destroy($id)
    {
        // Cari transaksi berdasarkan ID
        $transaction = Transaction::findOrFail($id);

        // Hapus data dari database
        $transaction->delete();

        // Kembali ke halaman sebelumnya dengan pesan sukses
        return back()->with('success', 'Transaksi berhasil dihapus!');
    }

    public function stripeProcess(Request $request)
    {
        // 1. Setup Stripe
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // 2. Ambil Data Produk
        $variant = Variant::findOrFail($request->variant_id); 
        $productName = $variant->product->name ?? 'Produk ABUSER';
        
        $quantity = max(1, (int) $request->input('quantity', 1));

        // 3. Bersihkan Harga IDR (Hapus Rp & Titik)
        $priceIdrPerUnit = (int) preg_replace('/[^0-9]/', '', $variant->price);
        $priceIdrTotal = $priceIdrPerUnit * $quantity;
        $priceIdr = $priceIdrTotal;

        // --- HITUNG DISKON (DALAM RUPIAH DULU) ---
        if ($request->promo_code) {
            $promo = Promo::where('code', $request->promo_code)->first();
            if ($promo) {
                if ($promo->type == 'fixed') {
                    $priceIdr -= $promo->value;
                } elseif ($promo->type == 'percent') {
                    $priceIdr -= ($priceIdr * ($promo->value / 100));
                }
            }
        }
        // Pastikan tidak minus
        if ($priceIdr < 0) $priceIdr = 0;

        // ---------------------------------------------------------
        // 4. KONVERSI KE DOLLAR (USD) - INI RAHASIANYA
        // ---------------------------------------------------------
        
        // Tentukan Kurs Sendiri (Lebih aman dilebihkan sedikit biar gak rugi potongan admin)
        // Misal kurs asli 15.800, kita set 16.200
        $kursDollar = 16200; 

        // Rumus: Rupiah / Kurs
        // Contoh: 50.000 / 16.200 = 3.08 Dollar
        $priceUsd = $priceIdr / $kursDollar;

        // Stripe butuh format SEN (Cents). Jadi dikali 100.
        // $3.08 -> 308 cents
        $amountInCents = round($priceUsd * 100);

        // SYARAT STRIPE: Minimal transaksi harus sekitar $0.50 (50 sen)
        if ($amountInCents < 50) {
            return back()->with('error', 'Nominal terlalu kecil untuk pembayaran Dollar.');
        }

        // 5. Buat Sesi Stripe (MATA UANG 'USD')
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd', // <--- KITA PAKSA JADI USD
                    'product_data' => [
                        'name' => $productName . ' - ' . $variant->duration,
                        'description' => 'Original Price: IDR ' . number_format($priceIdrTotal), // Info tambahan
                    ],
                    'unit_amount' => $amountInCents, // Kirim harga Dollar
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            'success_url' => route('payment.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => url('/'),
            'metadata' => [
                'user_id' => auth()->id(),
                'variant_id' => $variant->id,
                'quantity' => $quantity,
                'promo_code' => $request->promo_code
            ]
        ]);

        return redirect($session->url);
    }
}