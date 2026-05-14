<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | ABUSER STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/checkout.css') }}">
    <style>
        /* CSS Tambahan untuk Modal & User Indicator */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.9); z-index: 10000; justify-content: center; align-items: center; backdrop-filter: blur(10px); }
        .modal-box { background: #1b1e2b; border: 1px solid #2d3248; padding: 40px; border-radius: 24px; width: 90%; max-width: 400px; text-align: center; box-shadow: 0 0 30px rgba(0, 170, 255, 0.2); }
        .input-dark-modal { width: 100%; background: #0f1015; border: 2px solid #2d3248; color: white; padding: 15px; border-radius: 12px; text-align: center; margin-bottom: 20px; outline: none; }

        /* === TOMBOL KEMBALI MINIMALIS === */
        .back-wrapper { max-width: 1200px; margin: 30px auto 10px auto; padding: 0 20px; width: 100%; box-sizing: border-box; }
        .back-link-minimal { display: inline-flex; align-items: center; gap: 12px; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 1rem; letter-spacing: 0.5px; transition: 0.3s; }
        .back-link-minimal svg { width: 24px; height: 24px; stroke: currentColor; transition: 0.3s; }
        .back-link-minimal:hover { color: #00aaff; }
        .back-link-minimal:hover svg { transform: translateX(-6px); }

        /* === CSS SLIDER / CAROUSEL === */
        .left-column { display: flex; flex-direction: column; gap: 20px; }
        .carousel-container { position: relative; width: 100%; border-radius: 16px; overflow: hidden; background: #1b1e2b; border: 1px solid #2d3248; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .carousel-track { display: flex; transition: transform 0.4s cubic-bezier(0.25, 1, 0.5, 1); width: 100%; }
        .carousel-slide { min-width: 100%; box-sizing: border-box; }
        .carousel-slide img { width: 100%; display: block; aspect-ratio: 16/9; object-fit: cover; }
        .carousel-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(15, 16, 21, 0.7); color: white; border: 1px solid rgba(255,255,255,0.1); width: 40px; height: 40px; border-radius: 50%; cursor: pointer; z-index: 10; display: flex; align-items: center; justify-content: center; transition: 0.3s; backdrop-filter: blur(5px); }
        .carousel-btn:hover { background: #00aaff; color: black; border-color: #00aaff; }
        .carousel-btn.prev { left: 15px; }
        .carousel-btn.next { right: 15px; }
        
        .carousel-dots { position: absolute; bottom: 15px; left: 50%; transform: translateX(-50%); display: flex; gap: 8px; z-index: 10; }
        .dot { width: 10px; height: 10px; background: rgba(255,255,255,0.3); border-radius: 50%; cursor: pointer; transition: 0.3s; }
        .dot.active { background: #00aaff; transform: scale(1.3); }

        /* === CSS DESKRIPSI TERPISAH === */
        .desc-box-standalone { background: var(--bg-surface); padding: 32px; border-radius: 16px; border: 1px solid var(--border-color); box-shadow: 0 1px 3px rgba(0,0,0,0.05); }
        .desc-box-standalone h3 { color: var(--text-main); margin-top: 0; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 16px; font-weight: 800; font-size: 1.25rem; }
        .desc-box-standalone .desc-content { color: var(--text-muted); line-height: 1.6; font-size: 0.95rem; }

        /* LIGHTBOX CSS */
        .lightbox-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); z-index: 100000; align-items: center; justify-content: center; backdrop-filter: blur(10px); }
        .lightbox-content { position: relative; max-width: 90%; max-height: 90vh; }
        .lightbox-img { width: auto; max-width: 100%; max-height: 90vh; border-radius: 12px; object-fit: contain; }
        .lightbox-btn { position: absolute; top: 50%; transform: translateY(-50%); background: rgba(255,255,255,0.1); color: white; border: none; font-size: 2rem; width: 50px; height: 50px; cursor: pointer; border-radius: 50%; display: flex; align-items: center; justify-content: center; transition: 0.3s; }
        .lightbox-btn:hover { background: #00aaff; color: black; }
        .lightbox-prev { left: -60px; }
        .lightbox-next { right: -60px; }
        .lightbox-close { position: absolute; top: -40px; right: 0; background: none; border: none; color: white; font-size: 2rem; cursor: pointer; }
        @media(max-width: 768px) {
            .lightbox-prev { left: 10px; }
            .lightbox-next { right: 10px; }
            .lightbox-close { right: 10px; top: -30px; }
        }
    </style>
</head>
<body>
    <script>
        if (localStorage.getItem('abuser_theme') !== 'light') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>

    {{-- Flash Error Display --}}
    @if(session('error'))
    <div id="flashError" style="
        position: fixed; top: 20px; left: 50%; transform: translateX(-50%);
        background: #1e0a0a; border: 1px solid #ef4444; color: #fca5a5;
        padding: 14px 24px; border-radius: 12px; z-index: 99999;
        font-size: 0.9rem; font-weight: 600; max-width: 90vw;
        display: flex; align-items: center; gap: 10px;
        box-shadow: 0 8px 32px rgba(239,68,68,0.3);
        animation: slideDown 0.3s ease;
    ">
        <span style="font-size:1.1rem;">⚠️</span>
        {{ session('error') }}
        <button onclick="this.parentElement.remove()" style="background:none;border:none;color:#fca5a5;cursor:pointer;font-size:1rem;margin-left:8px;">✕</button>
    </div>
    <style>@keyframes slideDown { from { opacity:0; top:0; } to { opacity:1; top:20px; } }</style>
    @endif
    <div class="modal-overlay" id="loginModal">
        <div class="modal-box">
            <h2 style="margin-top:0; color:var(--text-main); font-weight:800; letter-spacing: -0.5px;">LOGIN REQUIRED</h2>
            <p style="color:var(--text-muted); font-size:0.95rem; margin-bottom:24px;">Please login with Discord to proceed with your purchase.</p>
            <a href="{{ route('auth.discord') }}?redirect_id={{ $product->id }}" class="btn-discord-login" style="background: #5865F2; color: #fff; border: none; width: 100%; padding: 16px; border-radius: 12px; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: 12px; text-decoration: none; transition: 0.2s; margin-top: 16px; font-size: 1rem;">
                <svg width="24" height="24" viewBox="0 0 127.14 96.36" fill="currentColor">
                    <path d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,46,53.89,53,48.84,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.31,60,73.31,53s5-12.74,11.43-12.74S96.3,46,96.19,53,91.08,65.69,84.69,65.69Z"/>
                </svg>
                LOGIN WITH DISCORD
            </a>
            <button onclick="closeModal('loginModal')" style="margin-top: 20px; background: transparent; color: var(--text-muted); border: none; cursor: pointer; text-decoration: underline; font-weight: 500;">Cancel</button>
        </div>
    </div>



    <nav class="navbar anim-fade-down">
        <div style="display:flex; align-items:center; gap:16px;">
            <a href="/" class="brand">ABUSER <span>STORE</span></a>
            <a href="{{ route('reviews.index') }}" class="reviews-nav-btn">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Reviews
            </a>
        </div>
        
        <div style="display: flex; gap: 20px; align-items: center;">
            @auth
                <div style="display: flex; align-items: center; gap: 10px; background: var(--bg-body); padding: 6px 16px; border-radius: 30px; border: 1px solid var(--border-color);">
                    <a href="{{ route('profile.show', ['name' => strtolower(Auth::user()->name), 'id' => Auth::user()->id]) }}" style="display: flex; align-items: center; gap: 10px; text-decoration: none;">
                        <img src="{{ Auth::user()->avatar }}" alt="Avatar" style="width: 28px; height: 28px; border-radius: 50%; border: 1px solid var(--border-color);">
                        <div style="display: flex; flex-direction: column; line-height: 1.2;">
                            <span style="color: var(--text-main); font-weight: 700; font-size: 0.8rem;">{{ strtoupper(Auth::user()->name) }}</span>
                            @if(Auth::user()->provider_name === 'discord')
                                <span style="color: #5865F2; font-size: 0.65rem; font-weight: 700;">DISCORD LINKED</span>
                            @else
                                <span style="color: var(--success); font-size: 0.65rem; font-weight: 700;">VERIFIED</span>
                            @endif
                        </div>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" style="margin-left: 8px; display: flex; align-items: center;">
                        @csrf
                        <button type="submit" style="background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1rem; font-weight: bold; padding: 0;">✕</button>
                    </form>
                </div>
            @endauth

            <button id="themeToggleBtn" onclick="toggleTheme()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding: 4px; display:flex;">
                <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <!-- Default Sun Icon -->
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </button>

            <div class="lang-wrapper">
                <div class="lang-selected" onclick="toggleLang()">
                    <img id="currentFlag" src="https://flagcdn.com/w20/us.png" alt="US">
                    <span id="currentLangText">EN</span>
                </div>
                <div class="lang-dropdown" id="langOptions">
                    <div class="lang-option" onclick="selectLang('id', 'ID', 'https://flagcdn.com/w20/id.png')">
                        <img src="https://flagcdn.com/w20/id.png" alt="ID"> ID
                    </div>
                    <div class="lang-option" onclick="selectLang('en', 'EN', 'https://flagcdn.com/w20/us.png')">
                        <img src="https://flagcdn.com/w20/us.png" alt="US"> EN
                    </div>
                </div>
            </div>
        </div>
    </nav>

    @php 
        $firstAvailable = $product->variants->filter(function($v) {
            return $v->vouchers->where('status', 'AVAILABLE')->count() > 0;
        })->first();
        
        $first = $firstAvailable ?? $product->variants->first();
        $first_stock = $first ? $first->vouchers->where('status', 'AVAILABLE')->count() : 0;

        if(!$first) {
            $first = (object)['id' => 0, 'price' => 0, 'price_usd' => 0, 'price_amount' => 0, 'currency' => 'USD', 'duration' => 'Data Rusak'];
            $first_stock = 0;
        }

        $currencySymbols = ['USD'=>'$','IDR'=>'Rp ','EUR'=>'€','GBP'=>'£','MYR'=>'RM ','SGD'=>'S$','THB'=>'฿','JPY'=>'¥','AUD'=>'A$'];

        // Hitung total gambar yang ada (1 Cover + Sisa slide dari DB)
        $totalSlides = 1 + $product->images->count();
    @endphp

    <div class="back-wrapper anim-fade delay-1">
        <a href="/" class="back-link-minimal" style="color: var(--text-muted);">
            <div style="width: 24px; height: 24px; flex-shrink: 0; display: flex; align-items: center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 100%; height: 100%;">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </div>
            <span class="lang-back">KEMBALI</span>
        </a>
    </div>

    <div class="container" id="checkoutContainer" style="opacity:0;">

        {{-- Skeleton: Left column --}}
        <div id="skLeft" class="left-column" style="display:flex; flex-direction:column; gap:20px;">
            <div class="skeleton sk-checkout-img"></div>
            <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:16px; padding:30px; display:flex; flex-direction:column; gap:14px;">
                <div class="skeleton sk-line w40"></div>
                <div class="skeleton sk-line w100"></div>
                <div class="skeleton sk-line w70"></div>
                <div class="skeleton sk-line w100"></div>
            </div>
        </div>

        {{-- Skeleton: Right column --}}
        <div id="skRight" style="background:var(--bg-surface); border:1px solid var(--border-color); padding:32px; border-radius:16px; display:flex; flex-direction:column; gap:16px;">
            <div class="skeleton sk-checkout-title"></div>
            <div class="skeleton sk-checkout-price"></div>
            <div class="skeleton sk-variant"></div>
            <div class="skeleton sk-variant"></div>
            <div class="skeleton sk-line w70"></div>
            <div class="skeleton sk-btn" style="margin-top:auto;"></div>
        </div>

        {{-- Real: Left column (hidden initially) --}}
        <div class="left-column" id="realLeft" style="display:none;">
            <div class="carousel-container">
                <div class="carousel-track" id="sliderTrack">
                    
                    <div class="carousel-slide" onclick="openLightbox(0)" style="cursor: pointer;">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}">
                        @else
                            <div style="aspect-ratio: 16/9; display:flex; align-items:center; justify-content:center; background:var(--bg-body); color:var(--text-muted); font-weight:600;">NO IMAGE</div>
                        @endif
                    </div>

                    @foreach($product->images as $index => $img)
                        <div class="carousel-slide" onclick="openLightbox({{ $index + 1 }})" style="cursor: pointer;">
                            <img src="{{ asset('storage/' . $img->image_path) }}" alt="Slider {{ $product->name }}">
                        </div>
                    @endforeach

                </div>
                
                @if($totalSlides > 1)
                    <button type="button" class="carousel-btn prev" onclick="moveSlide(-1)">❮</button>
                    <button type="button" class="carousel-btn next" onclick="moveSlide(1)">❯</button>
                    
                    <div class="carousel-dots">
                        <span class="dot active" onclick="currentSlide(0)"></span>
                        @foreach($product->images as $index => $img)
                            <span class="dot" onclick="currentSlide({{ $index + 1 }})"></span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="desc-box-standalone">
                <h3 class="lang-desc">Deskripsi Produk</h3>
                <div class="desc-content">
                    {!! $product->description !!}
                </div>
            </div>

        </div>
        {{-- End Real Left column --}}

        {{-- Real: Right column (order card, hidden initially) --}}
        <div id="realRight" style="display:none;">
        <form action="{{ route('payment.process') }}" method="POST" id="checkoutForm" class="order-card">
            @csrf
            <input type="hidden" name="variant_id" id="variantInput" value="{{ $first->id }}">
            <input type="hidden" name="quantity" id="quantityInput" value="1">
            <input type="hidden" name="payment_method" id="paymentMethodInput" value="">
            <input type="hidden" name="promo_code" id="appliedPromoInput" value="">
            <input type="hidden" name="currency" id="currencyInput" value="USD">

            <div style="display:flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <h1 class="product-title">{{ $product->name }}</h1>
                    <div class="product-price" id="mainPriceDisplay">
                        @php
                            $a   = $first->price_amount ?? $first->price_usd ?? 0;
                            $c   = $first->currency ?? 'USD';
                            $sym = $currencySymbols[$c] ?? ($c.' ');
                            $dec = in_array($c, ['IDR','JPY']) ? 0 : 2;
                        @endphp
                        {{ $sym . number_format($a, $dec) }}
                    </div>
                    
                    <div class="stock-badge">
                        <span class="lang-stock">Stok</span>: <span id="stockDisplay">{{ $first_stock }}</span> <span id="unitDisplay" class="lang-unit">@if($first_stock == 1) Unit @else Units @endif</span>
                    </div>
                </div>
            </div>

            <label class="form-label lang-step1">1. Pilih Durasi Paket</label>
            <div class="variant-grid">
                @foreach($product->variants as $v)
                    @php 
                        $stok = $v->vouchers->where('status', 'AVAILABLE')->count();
                        $isOutOfStock = $stok <= 0; 
                    @endphp
                    
                    <div class="variant-item {{ ($first->id == $v->id) ? 'active' : '' }} {{ $isOutOfStock ? 'disabled' : '' }}"
                         onclick="{{ $isOutOfStock ? '' : "selectVariant(this, '{$v->id}', '" . ($v->price_amount ?? $v->price_usd) . "', '{$stok}', '" . ($v->currency ?? 'USD') . "')" }}">
                        <div>
                            <div style="font-weight:700; font-size:1rem; color:var(--text-main);" class="lang-duration" data-val="{{ $v->duration }}">{{ $v->duration }}</div>
                            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">
                                <span class="lang-stock">Stok</span>: <span class="stock-num">{{ $stok }}</span> <span class="lang-unit">Unit</span>
                            </div>
                        </div>

                        @php
                            $vAmount = $v->price_amount ?? $v->price_usd ?? 0;
                            $vCur    = $v->currency ?? 'USD';
                            $vSym    = $currencySymbols[$vCur] ?? ($vCur.' ');
                            $vDec    = in_array($vCur, ['IDR','JPY']) ? 0 : 2;
                        @endphp
                        <span class="v-price-text lang-habis-or-price"
                              data-stock="{{ $stok }}"
                              data-price-amount="{{ $vAmount }}"
                              data-currency="{{ $vCur }}"
                              data-price-sym="{{ $vSym }}"
                              data-price-dec="{{ $vDec }}"
                              style="font-weight:800; color: {{ $isOutOfStock ? 'var(--danger)' : 'var(--primary)' }};">
                            {{ $isOutOfStock ? 'HABIS' : $vSym . number_format($vAmount, $vDec) }}
                        </span>
                    </div>
                @endforeach
            </div>

            <label class="form-label lang-step2">2. Jumlah & Promo</label>
            <div class="control-row">
                <div>
                    <div class="promo-wrapper">
                        <input type="text" id="promoInput" class="input-dark" placeholder="Kode Promo">
                        <button type="button" onclick="checkCoupon()" class="btn-apply">APPLY</button>
                    </div>
                    <div id="promoMessage" style="font-size:0.8rem; margin-top:5px; font-weight:bold;"></div>
                </div>

                <div class="qty-box">
                    <button type="button" class="qty-btn" onclick="updateQty(-1)">-</button>
                    <span id="qtyDisplay" style="font-weight:700; color:var(--text-main);">1</span>
                    <button type="button" class="qty-btn" onclick="updateQty(1)">+</button>
                </div>
            </div>

            <label class="form-label lang-step3">3. Metode Pembayaran</label>
            <div class="pm-grid">
                <div class="pm-item" onclick="selectPayment('QRIS', this)">📱 QRIS / BCA</div>
                <div class="pm-item" onclick="selectPayment('stripe', this)">💳 STRIPE</div>
            </div>

            <button type="button" class="btn-pay" id="btnPay" {{ ($first_stock <= 0) ? 'disabled' : '' }} onclick="handlePayment()">
                BAYAR SEKARANG
            </button>
        </form>
        </div>{{-- End #realRight --}}
    </div>

    <footer>
        <div style="margin-bottom: 12px;">
            <a href="{{ route('tos') }}" style="color: var(--text-muted); text-decoration: none; margin: 0 10px; font-size: 0.9rem; transition: 0.3s;">Terms of Service</a>
            |
            <a href="{{ route('privacy') }}" style="color: var(--text-muted); text-decoration: none; margin: 0 10px; font-size: 0.9rem; transition: 0.3s;">Privacy Policy</a>
        </div>
        &copy; {{ date('Y') }} ABUSER STORE. All Rights Reserved.
    </footer>

    <!-- LIGHTBOX MODAL -->
    <div class="lightbox-overlay" id="lightboxModal" onclick="closeLightbox(event)">
        <div class="lightbox-content">
            <button class="lightbox-close" onclick="document.getElementById('lightboxModal').style.display='none'">&times;</button>
            <button class="lightbox-btn lightbox-prev" onclick="changeLightbox(-1, event)">❮</button>
            <img src="" class="lightbox-img" id="lightboxImage">
            <button class="lightbox-btn lightbox-next" onclick="changeLightbox(1, event)">❯</button>
        </div>
    </div>

    <script>
        const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};

        function handlePayment() {
            if (!isLoggedIn) {
                document.getElementById('loginModal').style.display = 'flex';
                return false;
            }
            
            const paymentMethod = document.getElementById('paymentMethodInput').value;
            if (!paymentMethod) {
                const t = translations[currentLang];
                alert(t.no_pm);
                return false;
            }

            document.getElementById('checkoutForm').submit();
        }

        // === SCRIPT SLIDER / CAROUSEL ===
        let slideIndex = 0;
        const track = document.getElementById('sliderTrack');
        
        // Cek dulu apakah element dots-nya ada (karena bisa jadi hilang kalau gambarnya cuma 1)
        const dots = document.querySelectorAll('.dot');
        const totalSlides = document.querySelectorAll('.carousel-slide').length;

        function updateSlider() {
            if (totalSlides <= 1) return; // Kalo gambar cuma 1, gak usah jalanin animasi
            track.style.transform = `translateX(-${slideIndex * 100}%)`;
            dots.forEach(dot => dot.classList.remove('active'));
            dots[slideIndex].classList.add('active');
        }

        function moveSlide(n) {
            if (totalSlides <= 1) return;
            slideIndex += n;
            if (slideIndex >= totalSlides) slideIndex = 0;
            if (slideIndex < 0) slideIndex = totalSlides - 1;
            updateSlider();
        }

        function currentSlide(n) {
            if (totalSlides <= 1) return;
            slideIndex = n;
            updateSlider();
        }
        // ==================================

        // === LIGHTBOX LOGIC ===
        let lightboxIndex = 0;
        const lightboxImages = [];
        @if($product->image)
            lightboxImages.push("{{ asset('storage/' . $product->image) }}");
        @endif
        @foreach($product->images as $img)
            lightboxImages.push("{{ asset('storage/' . $img->image_path) }}");
        @endforeach

        function openLightbox(index) {
            if (lightboxImages.length === 0) return;
            lightboxIndex = index;
            document.getElementById('lightboxImage').src = lightboxImages[lightboxIndex];
            document.getElementById('lightboxModal').style.display = 'flex';
        }

        function changeLightbox(step, event) {
            event.stopPropagation();
            lightboxIndex += step;
            if (lightboxIndex >= lightboxImages.length) lightboxIndex = 0;
            if (lightboxIndex < 0) lightboxIndex = lightboxImages.length - 1;
            document.getElementById('lightboxImage').src = lightboxImages[lightboxIndex];
        }

        function closeLightbox(event) {
            if (event.target.id === 'lightboxModal') {
                document.getElementById('lightboxModal').style.display = 'none';
            }
        }

        // MODAL UTILS
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }

        // GLOBAL STATE
        let activePrice   = {{ $first->price_amount ?? $first->price_usd ?? 0 }};
        let activeCurrency = '{{ $first->currency ?? "USD" }}';
        let activeSym     = '{{ ["USD"=>"$","IDR"=>"Rp ","EUR"=>"€","GBP"=>"£","MYR"=>"RM ","SGD"=>"S$","THB"=>"฿","JPY"=>"¥","AUD"=>"A$"][$first->currency ?? "USD"] ?? "$ " }}';
        let activeDec     = {{ in_array($first->currency ?? 'USD', ['IDR','JPY']) ? 0 : 2 }};
        let quantity = 1;
        let currentStock = {{ $first_stock ?? 0 }};
        let activePromo = { type: null, value: 0, max_discount: 0 };
        let currentLang = 'en';

        function toggleLang() { document.getElementById('langOptions').classList.toggle('show'); }
        window.onclick = function(event) {
            if (!event.target.closest('.lang-wrapper')) document.getElementById('langOptions').classList.remove('show');
        }

        const translations = {
            'id': { desc: 'Deskripsi Produk', step1: '1. Pilih Durasi Paket', step2: '2. Jumlah & Promo', step3: '3. Metode Pembayaran', stock: 'Stok', unit: 'Unit', pay: 'BAYAR SEKARANG', sold: 'HABIS', promo_check: 'Mengecek...', promo_success: 'Kode Berhasil Dipasang!', promo_invalid: 'Kode Tidak Valid', promo_placeholder: 'Kode Promo', no_pm: 'Silakan pilih metode pembayaran terlebih dahulu!', back: 'KEMBALI', max_stock: 'Stok maksimal tercapai!' },
            'en': { desc: 'Product Description', step1: '1. Select Duration', step2: '2. Qty & Promo', step3: '3. Payment Method', stock: 'Stock', unit: 'Unit', pay: 'PAY NOW', sold: 'SOLD OUT', promo_check: 'Checking...', promo_success: 'Promo Code Applied!', promo_invalid: 'Invalid Code', promo_placeholder: 'Promo Code', no_pm: 'Please select a payment method first!', back: 'BACK', max_stock: 'Maximum stock reached!' }
        };

        function selectLang(lang, text, flagUrl) {
            document.getElementById('currentFlag').src = flagUrl;
            document.getElementById('currentLangText').innerText = text;
            document.getElementById('langOptions').classList.remove('show');
            localStorage.setItem('abuser_lang', lang);
            applyLanguage(lang);
        }

        function applyLanguage(lang) {
            currentLang = lang;
            const t = translations[lang];
            document.querySelector('.lang-desc').innerText = t.desc;
            document.querySelector('.lang-step1').innerText = t.step1;
            document.querySelector('.lang-step2').innerText = t.step2;
            document.querySelector('.lang-step3').innerText = t.step3;
            document.querySelector('.lang-back').innerText = t.back;
            document.querySelectorAll('.lang-stock').forEach(el => el.innerText = t.stock);
            
            document.querySelectorAll('.lang-unit').forEach(el => {
                 let stockEl = el.parentElement.querySelector('.stock-num') || el.parentElement;
                 let st = parseInt(stockEl.innerText.replace(/[^0-9]/g, '')) || 0;
                 if(lang === 'en') {
                     el.innerText = st == 1 ? 'Unit' : 'Units';
                 } else {
                     el.innerText = 'Unit';
                 }
            });
            document.querySelectorAll('.lang-duration').forEach(el => {
                let originalText = el.getAttribute('data-val');
                if (lang === 'en') {
                    el.innerText = originalText.replace(/Tahun/g, 'Year').replace(/Bulan/g, 'Month').replace(/Hari/g, 'Day').replace(/Minggu/g, 'Week').replace(/Selamanya/g, 'Lifetime');
                } else {
                    el.innerText = originalText;
                }
            });
            document.querySelectorAll('.lang-habis-or-price').forEach(el => {
                if (parseInt(el.getAttribute('data-stock')) <= 0) {
                    el.innerText = t.sold;
                }
            });
            document.getElementById('promoInput').placeholder = t.promo_placeholder;
            updatePayButton();
        }

        function updatePayButton() {
            const btn = document.getElementById('btnPay');
            const t = translations[currentLang];
            if (btn.disabled && currentStock <= 0) btn.innerText = t.sold;
            else btn.innerText = t.pay;
        }

        window.addEventListener('DOMContentLoaded', () => {
            const savedLang = localStorage.getItem('abuser_lang') || 'en';
            const flag = savedLang === 'id' ? 'https://flagcdn.com/w20/id.png' : 'https://flagcdn.com/w20/us.png';
            document.getElementById('currentFlag').src = flag;
            document.getElementById('currentLangText').innerText = savedLang.toUpperCase();
            applyLanguage(savedLang);

            // === SKELETON → REAL CONTENT REVEAL (Checkout) ===
            const container = document.getElementById('checkoutContainer');
            const skLeft    = document.getElementById('skLeft');
            const skRight   = document.getElementById('skRight');
            const realLeft  = document.getElementById('realLeft');
            const realRight = document.getElementById('realRight');

            // Show skeletons first (container visible, skeletons visible)
            container.style.transition = 'opacity 0.3s ease';
            container.style.opacity = '1';

            setTimeout(() => {
                // Fade out skeletons
                [skLeft, skRight].forEach(el => {
                    el.style.transition = 'opacity 0.3s ease';
                    el.style.opacity = '0';
                });
                setTimeout(() => {
                    skLeft.style.display  = 'none';
                    skRight.style.display = 'none';
                    realLeft.style.display  = 'flex';
                    realRight.style.display = 'block';
                    // Fade real content in
                    [realLeft, realRight].forEach(el => {
                        el.style.opacity = '0';
                        el.style.transition = 'opacity 0.4s ease';
                    });
                    requestAnimationFrame(() => {
                        requestAnimationFrame(() => {
                            realLeft.style.opacity  = '1';
                            realRight.style.opacity = '1';
                        });
                    });
                }, 320);
            }, 600);
        });

        function selectVariant(el, id, amount, stock, currency) {
            document.querySelectorAll('.variant-item').forEach(v => v.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('variantInput').value = id;
            // Find sym/dec from data attributes on the price span
            const priceSpan = el.querySelector('.v-price-text');
            activeSym      = priceSpan ? priceSpan.dataset.priceSym : '$';
            activeDec      = priceSpan ? parseInt(priceSpan.dataset.priceDec) : 2;
            activePrice    = parseFloat(amount) || 0;
            activeCurrency = currency || 'USD';
            currentStock   = parseInt(stock) || 0;
            document.getElementById('stockDisplay').innerText = currentStock;
            quantity = 1;
            document.getElementById('qtyDisplay').innerText = 1;
            document.getElementById('quantityInput').value = 1;
            document.getElementById('currencyInput').value = currency;
            const btn = document.getElementById('btnPay');
            btn.disabled = (currentStock <= 0);
            updatePayButton();
            calculateTotal();
        }

        function updateQty(change) {
            if (currentStock <= 0) return;
            let n = quantity + change;
            if (n < 1) n = 1;
            if (n > currentStock) { alert(translations[currentLang].max_stock); n = currentStock; }
            quantity = n;
            document.getElementById('qtyDisplay').innerText = n;
            document.getElementById('quantityInput').value = n;
            calculateTotal();
        }

        function calculateTotal() {
            let base     = activePrice;
            let subtotal = base * quantity;
            let disc     = 0;
            if (activePromo.type === 'percent') {
                disc = subtotal * (activePromo.value / 100);
                if (activePromo.max_discount > 0) {
                    // max_discount stored in IDR; convert if needed
                    let maxDiscNative = activeCurrency === 'IDR' ? activePromo.max_discount : activePromo.max_discount / 15500;
                    if (disc > maxDiscNative) disc = maxDiscNative;
                }
            } else if (activePromo.type === 'fixed') {
                // fixed discount stored in IDR
                disc = activeCurrency === 'IDR' ? activePromo.value : activePromo.value / 15500;
            }
            let final = Math.max(0, subtotal - disc);
            document.getElementById('mainPriceDisplay').innerText = activeSym + final.toFixed(activeDec);
        }

        async function checkCoupon() {
            const code = document.getElementById('promoInput').value;
            const msg = document.getElementById('promoMessage');
            const t = translations[currentLang];
            if(!code) return;
            msg.innerText = t.promo_check;
            msg.style.color = "var(--text-muted)";
            try {
                const res = await fetch("{{ route('payment.check_promo') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    body: JSON.stringify({ promo_code: code, variant_id: document.getElementById('variantInput').value, quantity: quantity, lang: currentLang })
                });
                const data = await res.json();
                if (data.success) {
                    msg.style.color = "var(--success)";
                    msg.innerText = "✅ " + t.promo_success; 
                    activePromo = { type: data.type, value: parseFloat(data.value), max_discount: parseFloat(data.max_discount || 0) };
                    document.getElementById('appliedPromoInput').value = code;
                } else {
                    msg.style.color = "var(--danger)";
                    msg.innerText = "❌ " + (data.message || t.promo_invalid);
                    activePromo = { type: null, value: 0, max_discount: 0 };
                    document.getElementById('appliedPromoInput').value = "";
                }
                calculateTotal();
            } catch (e) { console.error(e); }
        }

        function selectPayment(method, el) {
            document.querySelectorAll('.pm-item').forEach(i => i.classList.remove('active'));
            el.classList.add('active');
            document.getElementById('paymentMethodInput').value = method;
        }

        // --- DARK MODE LOGIC ---
        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark-mode');
            const icon = document.getElementById('themeIcon');
            if (isDark) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />';
            }
        }
        function toggleTheme() {
            document.documentElement.classList.toggle('dark-mode');
            localStorage.setItem('abuser_theme', document.documentElement.classList.contains('dark-mode') ? 'dark' : 'light');
            updateThemeIcon();
        }
        document.addEventListener('DOMContentLoaded', updateThemeIcon);
    </script>

    {{-- === FLOATING DISCORD BUTTON === --}}
    <style>
        .discord-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #5865F2;
            color: white;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(88, 101, 242, 0.4);
            z-index: 9999;
            transition: all 0.3s cubic-bezier(0.25, 1, 0.5, 1);
            animation: pulse-discord 2s infinite;
        }
        .discord-float:hover {
            transform: scale(1.1) translateY(-5px);
            box-shadow: 0 8px 25px rgba(88, 101, 242, 0.6);
            animation: none;
        }
        .discord-float svg {
            width: 32px;
            height: 32px;
            fill: white;
            transition: 0.3s;
        }
        .discord-float::before {
            content: "Join Community!";
            position: absolute;
            right: 75px;
            background: #1e2235;
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 700;
            white-space: nowrap;
            opacity: 0;
            visibility: hidden;
            transition: 0.3s;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
            border: 1px solid rgba(88,101,242,0.3);
            pointer-events: none;
        }
        .discord-float::after {
            content: "";
            position: absolute;
            right: 69px;
            top: 50%;
            transform: translateY(-50%);
            border-width: 6px;
            border-style: solid;
            border-color: transparent transparent transparent #1e2235;
            opacity: 0;
            visibility: hidden;
            transition: 0.3s;
        }
        .discord-float:hover::before,
        .discord-float:hover::after {
            opacity: 1;
            visibility: visible;
            right: 80px;
        }
        .discord-float:hover::after { right: 74px; }
        @keyframes pulse-discord {
            0%   { box-shadow: 0 0 0 0 rgba(88, 101, 242, 0.7); }
            70%  { box-shadow: 0 0 0 15px rgba(88, 101, 242, 0); }
            100% { box-shadow: 0 0 0 0 rgba(88, 101, 242, 0); }
        }
        @media (max-width: 768px) {
            .discord-float { bottom: 20px; right: 20px; width: 50px; height: 50px; }
            .discord-float svg { width: 26px; height: 26px; }
            .discord-float::before, .discord-float::after { display: none; }
        }
    </style>
    <a href="https://discord.com/invite/Js7Kpu8wWT" target="_blank" class="discord-float">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.14 96.36">
            <path d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,46,53.89,53,48.84,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.31,60,73.31,53s5-12.74,11.43-12.74S96.3,46,96.19,53,91.08,65.69,84.69,65.69Z"/>
        </svg>
    </a>
</body>
</html>