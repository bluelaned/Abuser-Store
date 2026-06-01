<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order | ABUSER STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <style>
        .track-container { max-width: 600px; margin: 0 auto; padding: 60px 20px; }
        .result-card { background: var(--bg-surface, rgba(255,255,255,0.03)); border: 1px solid var(--border-color); border-radius: 16px; padding: 28px; margin-top: 24px; display: none; }
        .result-card.show { display: block; }
        .status-step { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--border-color); }
        .status-step:last-child { border-bottom: none; }
        .step-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    </style>
</head>
<body>
    <script>
        if (localStorage.getItem('abuser_theme') !== 'light') document.documentElement.classList.add('dark-mode');
    </script>

    <nav class="navbar">
        <div style="display:flex;align-items:center;gap:16px;">
            <a href="/" class="brand">ABUSER <span>STORE</span></a>
        </div>
        <div style="display:flex;gap:20px;align-items:center;">
            @auth
            <a href="{{ route('my.orders') }}" style="color:var(--text-muted);font-size:0.875rem;text-decoration:none;">My Orders</a>
            @endauth
            <button onclick="toggleTheme()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;padding:4px;display:flex;">
                <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </button>
        </div>
    </nav>

    <div class="track-container">
        <div style="text-align:center;margin-bottom:36px;">
            <h1 style="font-size:2rem;font-weight:800;color:var(--text-main);margin:0 0 10px;">🔍 Track Your Order</h1>
            <p style="color:var(--text-muted);font-size:0.95rem;">Enter your invoice number to check the status of your order.</p>
        </div>

        <div style="display:flex;gap:10px;">
            <input type="text" id="refInput" placeholder="e.g. INV-1234567890-123" style="flex:1;padding:12px 16px;border:1px solid var(--border-color);border-radius:10px;background:var(--bg-body);color:var(--text-main);font-size:0.95rem;outline:none;transition:border-color 0.2s;" onfocus="this.style.borderColor='var(--primary)'" onblur="this.style.borderColor='var(--border-color)'" onkeydown="if(event.key==='Enter')checkOrder()">
            <button onclick="checkOrder()" id="checkBtn" style="padding:12px 24px;background:var(--primary);color:white;border:none;border-radius:10px;font-weight:700;font-size:0.9rem;cursor:pointer;transition:0.2s;white-space:nowrap;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">Check Status</button>
        </div>

        <div id="loadingState" style="text-align:center;padding:24px;display:none;color:var(--text-muted);">
            <div style="width:32px;height:32px;border:3px solid var(--border-color);border-top-color:var(--primary);border-radius:50%;animation:spin 0.8s linear infinite;margin:0 auto 10px;"></div>
            Checking...
        </div>
        <style>@keyframes spin{to{transform:rotate(360deg)}}</style>

        <div id="errorState" style="text-align:center;padding:24px;display:none;color:#ef4444;">
            <div style="font-size:2rem;margin-bottom:8px;">❌</div>
            <div id="errorMsg" style="font-weight:600;">Order not found.</div>
            <div style="font-size:0.82rem;color:var(--text-muted);margin-top:4px;">Double-check your invoice number and try again.</div>
        </div>

        <div class="result-card" id="resultCard">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
                <div>
                    <div style="font-size:0.78rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Invoice</div>
                    <div id="resRef" style="font-family:monospace;font-size:0.95rem;font-weight:700;color:var(--primary);margin-top:2px;"></div>
                </div>
                <span id="resBadge" style="padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:700;text-transform:uppercase;"></span>
            </div>

            <div class="status-step">
                <div class="step-dot" style="background:#6366f1;"></div>
                <div>
                    <div style="font-size:0.8rem;font-weight:600;color:var(--text-main);">Product</div>
                    <div id="resProduct" style="font-size:0.9rem;color:var(--text-muted);margin-top:2px;"></div>
                </div>
            </div>
            <div class="status-step">
                <div class="step-dot" style="background:#00c6ff;"></div>
                <div>
                    <div style="font-size:0.8rem;font-weight:600;color:var(--text-main);">Payment Method</div>
                    <div id="resPayment" style="font-size:0.9rem;color:var(--text-muted);margin-top:2px;"></div>
                </div>
            </div>
            <div class="status-step">
                <div class="step-dot" style="background:#f59e0b;"></div>
                <div>
                    <div style="font-size:0.8rem;font-weight:600;color:var(--text-main);">Order Date</div>
                    <div id="resDate" style="font-size:0.9rem;color:var(--text-muted);margin-top:2px;"></div>
                </div>
            </div>

            <div id="voucherSection" style="display:none;margin-top:16px;padding-top:16px;border-top:1px solid var(--border-color);">
                <div style="font-size:0.78rem;font-weight:700;color:var(--text-muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:8px;">🔑 Your Voucher(s)</div>
                <div id="resVouchers"></div>
            </div>

            <div id="pendingNote" style="display:none;margin-top:16px;padding:12px 16px;background:rgba(234,179,8,0.1);border:1px solid rgba(234,179,8,0.3);border-radius:8px;font-size:0.82rem;color:#d97706;">
                ⏳ Your payment is being processed. Voucher codes will be delivered once payment is confirmed.
            </div>
        </div>
    </div>

    <footer style="text-align:center;padding:40px 20px;color:var(--text-muted);font-size:0.875rem;">
        &copy; {{ date('Y') }} ABUSER STORE. All Rights Reserved.
    </footer>

    <script>
        function toggleTheme() {
            document.documentElement.classList.toggle('dark-mode');
            localStorage.setItem('abuser_theme', document.documentElement.classList.contains('dark-mode') ? 'dark' : 'light');
        }

        async function checkOrder() {
            const ref = document.getElementById('refInput').value.trim();
            if (!ref) return;

            document.getElementById('loadingState').style.display = 'block';
            document.getElementById('errorState').style.display = 'none';
            document.getElementById('resultCard').classList.remove('show');

            try {
                const res = await fetch(`/order-status/${encodeURIComponent(ref)}/check`);
                const data = await res.json();

                document.getElementById('loadingState').style.display = 'none';

                if (!data.found) {
                    document.getElementById('errorState').style.display = 'block';
                    document.getElementById('errorMsg').textContent = data.message || 'Order not found.';
                    return;
                }

                const statusColors = { PAID:'#22c55e', UNPAID:'#f59e0b', FAILED:'#ef4444', EXPIRED:'#94a3b8', FRAUD:'#ef4444' };
                const statusBg     = { PAID:'rgba(34,197,94,0.12)', UNPAID:'rgba(234,179,8,0.12)', FAILED:'rgba(239,68,68,0.12)', EXPIRED:'rgba(148,163,184,0.12)', FRAUD:'rgba(239,68,68,0.12)' };

                document.getElementById('resRef').textContent = data.reference;
                document.getElementById('resProduct').textContent = data.product_name + ' (x' + data.quantity + ')';
                document.getElementById('resPayment').textContent = data.payment_method;
                document.getElementById('resDate').textContent = data.created_at;

                const badge = document.getElementById('resBadge');
                badge.textContent = data.status;
                badge.style.color = statusColors[data.status] || '#94a3b8';
                badge.style.background = statusBg[data.status] || 'rgba(148,163,184,0.12)';

                if (data.vouchers) {
                    const vDiv = document.getElementById('resVouchers');
                    vDiv.innerHTML = '';
                    data.vouchers.split(', ').forEach(code => {
                        const span = document.createElement('span');
                        span.textContent = code.trim();
                        span.style.cssText = 'font-family:monospace;background:rgba(34,197,94,0.1);border:1px solid rgba(34,197,94,0.3);color:#22c55e;padding:4px 10px;border-radius:6px;font-size:0.82rem;display:inline-block;margin:2px 4px 2px 0;cursor:pointer;';
                        span.onclick = () => { navigator.clipboard.writeText(code.trim()); span.textContent = '✓ Copied!'; setTimeout(() => span.textContent = code.trim(), 1500); };
                        vDiv.appendChild(span);
                    });
                    document.getElementById('voucherSection').style.display = 'block';
                } else {
                    document.getElementById('voucherSection').style.display = 'none';
                }

                document.getElementById('pendingNote').style.display = (data.status === 'UNPAID') ? 'block' : 'none';
                document.getElementById('resultCard').classList.add('show');

            } catch(e) {
                document.getElementById('loadingState').style.display = 'none';
                document.getElementById('errorState').style.display = 'block';
                document.getElementById('errorMsg').textContent = 'Network error. Please try again.';
            }
        }
    </script>
</body>
</html>
