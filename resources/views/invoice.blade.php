<!DOCTYPE html>
<html lang="id">
<head>
    <title>Invoice {{ $transaction->reference }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #3b82f6;
            --primary-hover: #2563eb;
            --bg-body: #f8fafc;
            --bg-surface: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --success: #10b981;
            --warning: #f59e0b;
        }
        body { background: var(--bg-body); color: var(--text-main); font-family: 'Inter', sans-serif; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; padding: 20px; box-sizing: border-box; }
        .card { background: var(--bg-surface); padding: 40px; border-radius: 24px; border: 1px solid var(--border-color); text-align: center; max-width: 440px; width: 100%; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); }
        
        h1 { font-size: 1.25rem; color: var(--text-main); margin: 0 0 8px 0; font-weight: 800; }
        .amount { font-size: 2.5rem; font-weight: 900; color: var(--primary); margin: 16px 0; letter-spacing: -1px; }
        .unique-note { background: #fef3c7; color: #b45309; padding: 16px; border-radius: 12px; font-size: 0.95rem; margin-bottom: 24px; font-weight: 500; border: 1px solid #fde68a; line-height: 1.5; }
        
        .qris-box { background: white; padding: 16px; border-radius: 16px; display: inline-block; margin-bottom: 24px; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .qris-img { width: 100%; max-width: 250px; display: block; border-radius: 8px; }
        
        .timer { font-size: 0.95rem; color: var(--text-muted); margin-bottom: 24px; font-weight: 500; }
        
        .btn-wa { display: block; width: 100%; background: #25d366; color: white; padding: 16px; text-decoration: none; border-radius: 12px; font-weight: 700; margin-top: 16px; transition: 0.2s; font-size: 1.05rem; }
        .btn-wa:hover { background: #16a34a; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3); }
        
        .back-link { display: inline-block; margin-top: 24px; color: var(--text-muted); text-decoration: none; font-size: 0.95rem; font-weight: 600; transition: 0.2s; }
        .back-link:hover { color: var(--text-main); }
    </style>
</head>
<body>

    <div class="card">
        <h1>Menunggu Pembayaran</h1>
        <p style="color:var(--text-muted); font-size:0.95rem; margin:0; font-weight: 500;">Order ID: <span style="color: var(--text-main); font-weight: 700;">#{{ $transaction->reference }}</span></p>

        <div class="amount">Rp {{ number_format($transaction->price, 0, ',', '.') }}</div>

        <div class="unique-note">
            ⚠️ <b>PENTING:</b> Transfer TEPAT sampai <b>{{ $transaction->unique_code }} rupiah</b> terakhir agar terbaca sistem secara otomatis.
        </div>

        @if($transaction->payment_method == 'QRIS')
            <div class="qris-box">
                <img src="{{ asset('images/qris.jpg') }}" class="qris-img" alt="Scan QRIS">
            </div>
            <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.5; margin-bottom: 0;">Scan QRIS di atas menggunakan Dana, OVO, GoPay, atau Mobile Banking.</p>
        @else
            <div style="background:#f8fafc; padding:24px; border-radius:16px; border:1px solid var(--border-color); margin-bottom:24px;">
                <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/5/5c/Bank_Central_Asia.svg/1200px-Bank_Central_Asia.svg.png" height="24" style="margin-bottom:16px;">
                <div style="font-size:1.5rem; font-weight:900; color:var(--text-main); letter-spacing: 1px;">1234-5678-90</div>
                <div style="color:var(--text-muted); margin-top: 4px; font-weight: 500;">a.n Admin Abuser Store</div>
            </div>
        @endif

        <div class="timer">Selesaikan pembayaran dalam 15 menit</div>

        <a href="https://wa.me/6281234567890?text=Halo%20Admin,%20saya%20sudah%20transfer%20sebesar%20Rp%20{{ number_format($transaction->price, 0, ',', '.') }}%20untuk%20Order%20{{ $transaction->reference }}" class="btn-wa">
            Konfirmasi WhatsApp
        </a>
        
        <a href="{{ url('/') }}" class="back-link">Kembali ke Beranda</a>
    </div>

</body>
</html>