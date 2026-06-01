<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Success – ABUSER STORE</title>
    <meta name="description" content="ABUSER STORE payment invoice confirmed">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body:    #0a0b0f;
            --bg-surface: #13151e;
            --bg-card:    #1b1e2b;
            --border:     #2a2d3e;
            --primary:    #00c6ff;
            --primary2:   #0072ff;
            --success:    #00e676;
            --text-main:  #ffffff;
            --text-muted: #8892a4;
            --radius:     20px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 16px 60px;
        }

        /* ─── NAVBAR ─── */
        .navbar {
            width: 100%;
            max-width: 780px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }
        .brand { font-size: 1.3rem; font-weight: 900; letter-spacing: -0.5px; text-decoration: none; color: var(--text-main); }
        .brand span { color: var(--primary); }
        .nav-home {
            font-size: 0.85rem; font-weight: 600; color: var(--text-muted);
            text-decoration: none; border: 1px solid var(--border); padding: 8px 18px;
            border-radius: 30px; transition: .2s;
        }
        .nav-home:hover { border-color: var(--primary); color: var(--primary); }

        /* ─── CONTAINER ─── */
        .invoice-wrap {
            width: 100%;
            max-width: 780px;
            animation: fadeUp .5s ease both;
        }
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(30px); }
            to   { opacity:1; transform:translateY(0); }
        }

        /* ─── SUCCESS HEADER ─── */
        .success-header {
            background: linear-gradient(135deg, #0d1f12 0%, #0a1e2e 100%);
            border: 1px solid rgba(0,230,118,.25);
            border-radius: var(--radius) var(--radius) 0 0;
            padding: 40px 48px;
            display: flex;
            align-items: center;
            gap: 28px;
        }
        .check-circle {
            width: 72px; height: 72px; border-radius: 50%;
            background: rgba(0,230,118,.12);
            border: 2px solid var(--success);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
            animation: popIn .5s .1s cubic-bezier(.34,1.56,.64,1) both;
        }
        @keyframes popIn {
            from { transform: scale(0); opacity:0; }
            to   { transform: scale(1); opacity:1; }
        }
        .check-circle svg { width:34px; height:34px; }
        .success-text h1 { font-size: 1.7rem; font-weight:900; letter-spacing:-.5px; color: var(--text-main); }
        .success-text p  { color: var(--text-muted); font-size:.95rem; margin-top:6px; }

        /* ─── INVOICE CARD ─── */
        .invoice-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-top: none;
            border-radius: 0 0 var(--radius) var(--radius);
            padding: 0 48px 40px;
        }

        /* ─── DIVIDER ─── */
        .divider {
            height:1px; background: var(--border); margin: 28px 0;
        }
        .divider-dashed {
            height:1px;
            background: repeating-linear-gradient(90deg, var(--border) 0, var(--border) 8px, transparent 8px, transparent 14px);
            margin: 28px 0;
        }

        /* ─── INVOICE META ─── */
        .invoice-meta {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-top: 32px;
            gap: 20px;
        }
        .invoice-meta-left .label { font-size:.75rem; font-weight:700; letter-spacing:1px; color:var(--text-muted); text-transform:uppercase; }
        .invoice-meta-left .value { font-size:1.4rem; font-weight:900; color:var(--primary); margin-top:4px; font-family:monospace; letter-spacing:.5px; }
        .invoice-meta-right { text-align:right; }
        .invoice-meta-right .label { font-size:.75rem; font-weight:700; letter-spacing:1px; color:var(--text-muted); text-transform:uppercase; }
        .invoice-meta-right .value { font-size:.95rem; font-weight:600; color:var(--text-main); margin-top:4px; }

        /* ─── BADGE ─── */
        .badge-paid {
            display: inline-flex; align-items:center; gap:6px;
            background: rgba(0,230,118,.12); border:1px solid rgba(0,230,118,.3);
            color: var(--success); font-size:.78rem; font-weight:700;
            padding: 5px 14px; border-radius:30px; letter-spacing:.5px;
        }
        .badge-paid::before { content:''; width:7px; height:7px; background:var(--success); border-radius:50%; display:block; animation: blink 1.5s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:.3} }

        /* ─── TABLE ─── */
        .item-table { width:100%; border-collapse:collapse; }
        .item-table thead th {
            font-size:.72rem; font-weight:700; letter-spacing:1px; text-transform:uppercase;
            color:var(--text-muted); padding:0 0 12px; text-align:left;
        }
        .item-table thead th:last-child { text-align:right; }
        .item-table tbody tr td {
            padding: 14px 0;
            border-top: 1px solid var(--border);
            vertical-align: top;
        }
        .item-table .item-name  { font-weight:700; color:var(--text-main); font-size:1rem; }
        .item-table .item-sub   { font-size:.82rem; color:var(--text-muted); margin-top:3px; }
        .item-table .item-price { text-align:right; font-weight:700; color:var(--text-main); font-size:1rem; white-space:nowrap; }
        .item-table .item-qty   { color:var(--text-muted); font-size:.88rem; width:60px; text-align:center; }

        /* ─── TOTALS ─── */
        .totals-block { margin-left:auto; width:100%; max-width:320px; }
        .total-row { display:flex; justify-content:space-between; align-items:center; padding:7px 0; }
        .total-row .t-label { color:var(--text-muted); font-size:.9rem; }
        .total-row .t-value { font-weight:600; color:var(--text-main); font-size:.9rem; }
        .total-row.grand    { border-top:1px solid var(--border); margin-top:6px; padding-top:14px; }
        .total-row.grand .t-label { font-size:1rem; font-weight:700; color:var(--text-main); }
        .total-row.grand .t-value { font-size:1.35rem; font-weight:900; color:var(--primary); }

        /* ─── PAYMENT & CUSTOMER INFO ─── */
        .info-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
        .info-block { background:var(--bg-surface); border:1px solid var(--border); border-radius:14px; padding:20px 22px; }
        .info-block .info-title { font-size:.72rem; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:var(--text-muted); margin-bottom:12px; }
        .info-row { display:flex; justify-content:space-between; gap:10px; margin-bottom:8px; }
        .info-row:last-child { margin-bottom:0; }
        .info-row .i-label { color:var(--text-muted); font-size:.83rem; }
        .info-row .i-value { font-size:.83rem; font-weight:600; color:var(--text-main); text-align:right; word-break:break-all; }

        /* ─── ACTION BUTTONS ─── */
        .action-row { display:flex; gap:14px; margin-top:32px; }
        .btn-pdf {
            flex:1; display:flex; align-items:center; justify-content:center; gap:10px;
            background: linear-gradient(135deg, var(--primary), var(--primary2));
            color:#fff; font-weight:700; font-size:.95rem;
            border:none; border-radius:14px; padding:16px;
            cursor:pointer; transition:.2s; letter-spacing:.3px;
        }
        .btn-pdf:hover { opacity:.88; transform:translateY(-1px); }
        .btn-home {
            flex:1; display:flex; align-items:center; justify-content:center; gap:10px;
            background:transparent; color:var(--text-muted); font-weight:600; font-size:.95rem;
            border:1px solid var(--border); border-radius:14px; padding:16px;
            text-decoration:none; transition:.2s;
        }
        .btn-home:hover { border-color:var(--primary); color:var(--primary); }

        /* ─── FOOTER ─── */
        .invoice-footer {
            margin-top:12px; text-align:center; color:var(--text-muted); font-size:.78rem;
            padding: 20px 0;
        }

        /* ─── PRINT / PDF STYLES ─── */
        @media print {
            @page { margin: 10mm; size: A4; }
            body { background:#fff !important; color:#000 !important; padding:0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .navbar, .action-row, .success-header { display:none !important; }
            .invoice-wrap { max-width:100%; margin: 0; padding: 0; background: #fff !important; }
            .invoice-card { border:none !important; padding:0 !important; background: #fff !important; box-shadow: none !important; }
            .invoice-banner { display: block !important; width: 100%; height: auto; margin-bottom: 15px; }
            .divider, .divider-dashed { background:#ddd !important; margin: 15px 0 !important; height: 1px !important; }
            .invoice-meta { padding-top: 0; gap: 10px; }
            .invoice-meta-left .label, .invoice-meta-right .label, .item-table thead th, .total-row .t-label, .info-block .info-title, .info-row .i-label { color:#555 !important; }
            .invoice-meta-left .value { color:#000 !important; font-size: 1.4rem; }
            .invoice-meta-right .value, .item-table .item-name, .total-row .t-value, .info-row .i-value, .item-table .item-price { color:#000 !important; }
            .item-table tbody tr td { border-top-color:#ddd !important; padding: 8px 0 !important; }
            .item-table .item-sub, .item-table .item-qty { color:#555 !important; }
            .totals-block .total-row.grand .t-value { color:#000 !important; font-size: 1.4rem; }
            .totals-block .total-row.grand { border-top-color:#ddd !important; padding-top: 8px !important; margin-top: 4px !important; }
            .badge-paid { background:transparent !important; border:2px solid #16a34a !important; color:#16a34a !important; padding: 4px 8px !important; font-size: 0.85rem !important; font-weight: 800 !important; }
            .badge-paid::before { display: none; }
            .info-block { background:transparent !important; border:1px solid #ddd !important; border-radius: 4px !important; padding: 12px !important; }
            .info-grid { gap: 15px !important; }
            .invoice-footer { color:#555 !important; border-top: 1px solid #ddd !important; margin-top: 20px !important; padding-top: 10px !important; font-weight: normal !important; background: #fff !important; }

            /* Redeem Codes */
            .redeem-box { background: transparent !important; border: 1px dashed #aaa !important; }
            .redeem-title { color: #555 !important; }
            .redeem-code { color: #000 !important; font-size: 1.25rem !important; }
        }

        @media (max-width:600px) {
            .success-header { padding:24px; flex-direction:column; text-align:center; }
            .invoice-card   { padding:0 20px 28px; }
            .invoice-meta   { flex-direction:column; gap:16px; }
            .invoice-meta-right { text-align:left; }
            .info-grid      { grid-template-columns:1fr; }
            .action-row     { flex-direction:column; }
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="/" class="brand">ABUSER <span>STORE</span></a>
    <a href="/" class="nav-home">← Back to Store</a>
    </nav>

    <div class="invoice-wrap">

        {{-- ── SUCCESS HEADER ── --}}
        <div class="success-header">
            <div class="check-circle">
                <svg viewBox="0 0 24 24" fill="none" stroke="#00e676" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
            </div>
            <div class="success-text">
                <h1>Payment Successful! 🎉</h1>
                <p>Thank you for your purchase. Your invoice and redeem codes are shown below.</p>
            </div>
        </div>

        {{-- ── INVOICE BODY ── --}}
        <img src="{{ asset('images/custom_banner_2.png') }}" class="invoice-banner" style="display: none; width: 100%; border-radius: 0; margin-bottom: 15px;" alt="Invoice Banner">
        <div class="invoice-card">

            {{-- Meta --}}
            <div class="invoice-meta">
                <div class="invoice-meta-left">
                    <div class="label">Invoice No.</div>
                    <div class="value">{{ $order['order_id'] ?? 'N/A' }}</div>
                </div>
                <div class="invoice-meta-right">
                    <div style="margin-bottom:10px;">
                        <span class="badge-paid">PAID</span>
                    </div>
                    <div class="label">Payment Date</div>
                    <div class="value">{{ $order['paid_at'] ?? now()->format('d M Y, H:i') }} WIB</div>
                </div>
            </div>

            <div class="divider"></div>

            {{-- Item Table --}}
            <table class="item-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th class="item-qty">Qty</th>
                        <th style="text-align:right;">Unit Price</th>
                        <th style="text-align:right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="item-name">{{ $order['product_name'] ?? 'Produk ABUSER' }}</div>
                            <div class="item-sub">{{ $order['payment_method'] ?? 'Online Payment' }}</div>

                            {{-- Crypto pending confirmation state --}}
                            @if(!empty($order['crypto_pending']))
                            <div style="background:rgba(247,147,26,0.08); border:1px solid rgba(247,147,26,0.3); border-radius:16px; padding:24px 28px; margin-bottom:24px; text-align:center;">
                                <div style="font-size:2.5rem; margin-bottom:12px;">⏳</div>
                                <div style="font-weight:800; font-size:1.1rem; color:#f7931a; margin-bottom:8px;">Awaiting Crypto Confirmation</div>
                                <div style="font-size:0.875rem; color:var(--text-muted); line-height:1.7; margin-bottom:16px;">
                                    Your payment is being confirmed on the blockchain.<br>
                                    This usually takes <strong style="color:var(--text-main);">1–30 minutes</strong> depending on network congestion.<br>
                                    Voucher codes will be sent to your <strong style="color:#5865F2;">Discord DM</strong> automatically once confirmed.
                                </div>
                                <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
                                    <a href="{{ route('profile.show', ['name' => strtolower(auth()->user()->name ?? 'user'), 'id' => auth()->id() ?? 0]) }}#transactions"
                                       style="background:rgba(99,102,241,0.15); color:#6366f1; border:1px solid rgba(99,102,241,0.3); padding:9px 20px; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.85rem;">
                                        Check My Orders
                                    </a>
                                    @if(!empty($order['crypto_order_id']))
                                    <a href="{{ route('order.status') }}"
                                       style="background:rgba(247,147,26,0.12); color:#f7931a; border:1px solid rgba(247,147,26,0.3); padding:9px 20px; border-radius:8px; text-decoration:none; font-weight:700; font-size:0.85rem;">
                                        Track Order Status
                                    </a>
                                    @endif
                                </div>
                            </div>
                            @endif

                            @if(!empty($order['vouchers']))
                            <div class="redeem-box" style="margin-top: 12px; background: rgba(0, 198, 255, 0.1); border: 1px dashed var(--primary); padding: 10px 14px; border-radius: 8px;">
                                <div class="redeem-title" style="font-size: 0.75rem; color: var(--primary); font-weight: 700; text-transform: uppercase; margin-bottom: 6px;">🎟 Your Redeem Code(s):</div>
                                @foreach($order['vouchers'] as $vc)
                                    <div class="redeem-code" style="font-family: monospace; font-size: 1.1rem; color: #fff; font-weight: 700; letter-spacing: 1px; user-select: all;">{{ $vc }}</div>
                                @endforeach
                            </div>
                            @endif
                        </td>
                        <td class="item-qty">{{ $order['quantity'] ?? 1 }}</td>
                        <td class="item-price">
                            @if(($order['currency'] ?? 'USD') === 'IDR')
                                Rp {{ number_format($order['unit_price'] ?? 0, 0, ',', '.') }}
                            @else
                                $ {{ number_format($order['unit_price'] ?? 0, 2) }}
                            @endif
                        </td>
                        <td class="item-price">
                            @if(($order['currency'] ?? 'USD') === 'IDR')
                                Rp {{ number_format(($order['unit_price'] ?? 0) * ($order['quantity'] ?? 1), 0, ',', '.') }}
                            @else
                                $ {{ number_format(($order['unit_price'] ?? 0) * ($order['quantity'] ?? 1), 2) }}
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>

            <div class="divider-dashed"></div>

            {{-- Totals --}}
            <div style="display:flex; justify-content:flex-end;">
                <div class="totals-block">
                    @if(isset($order['discount']) && $order['discount'] > 0)
                    <div class="total-row">
                        <span class="t-label">Subtotal</span>
                        <span class="t-value">
                            @if(($order['currency'] ?? 'USD') === 'IDR')
                                Rp {{ number_format(($order['unit_price'] ?? 0) * ($order['quantity'] ?? 1), 0, ',', '.') }}
                            @else
                                $ {{ number_format(($order['unit_price'] ?? 0) * ($order['quantity'] ?? 1), 2) }}
                            @endif
                        </span>
                    </div>
                    <div class="total-row">
                        <span class="t-label" style="color:#00e676;">🎟 Discount ({{ $order['promo_code'] ?? '' }})</span>
                        <span class="t-value" style="color:#00e676;">
                            @if(($order['currency'] ?? 'USD') === 'IDR')
                                - Rp {{ number_format($order['discount'], 0, ',', '.') }}
                            @else
                                - $ {{ number_format($order['discount'], 2) }}
                            @endif
                        </span>
                    </div>
                    @endif
                    <div class="total-row grand">
                        <span class="t-label">TOTAL</span>
                        <span class="t-value">
                            @if(($order['currency'] ?? 'USD') === 'IDR')
                                Rp {{ number_format($order['total'] ?? 0, 0, ',', '.') }}
                            @else
                                $ {{ number_format($order['total'] ?? 0, 2) }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <div class="divider"></div>

            {{-- Info Grid --}}
            <div class="info-grid">
                <div class="info-block">
                    <div class="info-title">Payment Information</div>
                    <div class="info-row">
                        <span class="i-label">Method</span>
                        <span class="i-value">{{ $order['payment_method'] ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="i-label">Currency</span>
                        <span class="i-value">{{ $order['currency'] ?? 'USD' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="i-label">Status</span>
                        <span class="i-value" style="color:#00e676; font-weight:700;">✓ PAID</span>
                    </div>
                </div>
                <div class="info-block">
                    <div class="info-title">Customer Information</div>
                    <div class="info-row">
                        <span class="i-label">Email</span>
                        <span class="i-value">{{ $order['customer_email'] ?? '-' }}</span>
                    </div>
                    @if(isset($order['discord_name']))
                    <div class="info-row">
                        <span class="i-label">Discord</span>
                        <span class="i-value">{{ $order['discord_name'] }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="i-label">Date</span>
                        <span class="i-value">{{ $order['paid_at'] ?? now()->format('d M Y') }}</span>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="action-row">
                <button class="btn-pdf" onclick="downloadPDF()" id="btnPDF">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Save / Download PDF
                </button>
                <a href="/" class="btn-home">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Back to Store
                </a>
            </div>

        </div>

        <div class="invoice-footer">
            © {{ date('Y') }} ABUSER STORE · Need help? Contact us on Discord
        </div>

    </div>

    <script>
        function downloadPDF() {
            const btn = document.getElementById('btnPDF');
            btn.innerText = '⏳ Preparing PDF...';
            btn.disabled = true;
            setTimeout(() => {
                window.print();
                btn.innerHTML = `<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> Save / Download PDF`;
                btn.disabled = false;
            }, 300);
        }
    </script>

</body>
</html>
