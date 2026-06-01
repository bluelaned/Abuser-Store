@php
    $firstAvailable = $product->variants->filter(function($v) use ($product) {
        if ($product->delivery_method === 'gift') return true;
        return $v->vouchers->where('status', 'AVAILABLE')->count() > 0;
    })->first();

    $first = $firstAvailable ?? $product->variants->first();
    $first_stock = ($product->delivery_method === 'gift') ? 999999 : ($first ? $first->vouchers->where('status', 'AVAILABLE')->count() : 0);

    if(!$first) {
        $first = (object)['id' => 0, 'price' => 0, 'price_usd' => 0, 'price_amount' => 0, 'currency' => 'USD', 'duration' => 'N/A'];
        $first_stock = 0;
    }

    $currencySymbols = ['USD'=>'$','IDR'=>'Rp ','EUR'=>'€','GBP'=>'£','MYR'=>'RM ','SGD'=>'S$','THB'=>'฿','JPY'=>'¥','AUD'=>'A$'];
    $totalSlides = 1 + $product->images->count();
@endphp
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
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            20%      { transform: translateX(-6px); }
            40%      { transform: translateX(6px); }
            60%      { transform: translateX(-4px); }
            80%      { transform: translateX(4px); }
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

            <!-- NAVBAR BELL ICON -->
            <button id="annBellBtn" onclick="reopenAnn()" title="View Announcements" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding: 4px; display:none; position:relative;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                </svg>
                <span id="annBellDot" style="position:absolute; top:2px; right:4px; width:8px; height:8px; border-radius:50%; background:#ef4444; border:2px solid var(--bg-body); display:none;"></span>
            </button>

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


    {{-- ==================== STEP 1: PRODUCT DETAILS ==================== --}}

<div id="step1" style="max-width:1200px; margin:0 auto; padding:30px 20px; box-sizing:border-box;">

        {{-- Breadcrumb --}}
        <div style="font-size:0.8rem; color:var(--text-muted); margin-bottom:28px; display:flex; align-items:center; gap:6px;">
            <a href="/" style="color:var(--text-muted); text-decoration:none; transition:color .2s;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-muted)'">Home</a>
            <span style="opacity:.4;">/</span>
            <span style="opacity:.4;">Product</span>
            <span style="opacity:.4;">/</span>
            <span style="color:var(--text-main); font-weight:600;">{{ $product->name }}</span>
        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:40px; align-items:start;">

            {{-- LEFT: Image Gallery --}}
            <div>
                <div style="position:relative; border-radius:14px; overflow:hidden; background:var(--bg-surface); border:1px solid var(--border-color);">
                    <div id="sliderTrack" style="display:flex; transition:transform .4s cubic-bezier(.25,1,.5,1);">
                        @if($product->image)
                            <div style="min-width:100%; cursor:pointer;" onclick="openLightbox(0)">
                                <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" style="width:100%; display:block; aspect-ratio:16/9; object-fit:cover;">
                            </div>
                        @else
                            <div style="min-width:100%; aspect-ratio:16/9; display:flex; align-items:center; justify-content:center; color:var(--text-muted); font-weight:700; font-size:1.2rem;">NO IMAGE</div>
                        @endif
                        @foreach($product->images as $img)
                            <div style="min-width:100%; cursor:pointer;" onclick="openLightbox({{ $loop->index + 1 }})">
                                <img src="{{ asset('storage/' . $img->image_path) }}" alt="{{ $product->name }}" style="width:100%; display:block; aspect-ratio:16/9; object-fit:cover;">
                            </div>
                        @endforeach
                    </div>
                    @if($totalSlides > 1)
                        <button onclick="moveSlide(-1)" style="position:absolute;top:50%;left:12px;transform:translateY(-50%);background:rgba(0,0,0,.6);border:1px solid rgba(255,255,255,.1);color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:.2s;backdrop-filter:blur(4px);" onmouseover="this.style.background='var(--primary)'" onmouseout="this.style.background='rgba(0,0,0,.6)'">❮</button>
                        <button onclick="moveSlide(1)"  style="position:absolute;top:50%;right:12px;transform:translateY(-50%);background:rgba(0,0,0,.6);border:1px solid rgba(255,255,255,.1);color:#fff;width:36px;height:36px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1rem;transition:.2s;backdrop-filter:blur(4px);" onmouseover="this.style.background='var(--primary)'" onmouseout="this.style.background='rgba(0,0,0,.6)'">❯</button>
                    @endif
                </div>

                {{-- Thumbnails --}}
                @if($totalSlides > 1)
                <div style="display:flex; gap:10px; margin-top:12px; overflow-x:auto; padding-bottom:4px;">
                    <div onclick="currentSlide(0)" class="thumb-item active-thumb" style="flex-shrink:0;width:80px;height:54px;border-radius:8px;overflow:hidden;cursor:pointer;border:2px solid var(--primary);transition:.2s;">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" style="width:100%;height:100%;object-fit:cover;">
                        @endif
                    </div>
                    @foreach($product->images as $index => $img)
                    <div onclick="currentSlide({{ $index + 1 }})" class="thumb-item" style="flex-shrink:0;width:80px;height:54px;border-radius:8px;overflow:hidden;cursor:pointer;border:2px solid transparent;transition:.2s;" onmouseover="this.style.borderColor='rgba(255,255,255,.4)'" onmouseout="">
                        <img src="{{ asset('storage/' . $img->image_path) }}" style="width:100%;height:100%;object-fit:cover;">
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Description --}}
                @if($product->description)
                <div style="margin-top:20px; background:var(--bg-surface); border:1px solid var(--border-color); border-radius:14px; padding:24px;">
                    <h3 style="margin:0 0 12px; font-size:1rem; color:var(--text-main); font-weight:700; border-bottom:1px solid var(--border-color); padding-bottom:12px;" class="lang-desc">Product Description</h3>
                    <div style="color:var(--text-muted); font-size:0.9rem; line-height:1.7;">{!! $product->description !!}</div>
                </div>
                @endif
            </div>

            {{-- RIGHT: Product Info & Order --}}
            <div style="position:sticky; top:20px;">
                <h1 style="margin:0 0 6px; font-size:2rem; font-weight:800; color:var(--text-main); letter-spacing:-0.5px;">{{ $product->name }}</h1>
                <p style="margin:0 0 14px; color:var(--text-muted); font-size:0.9rem; font-weight:500;">{{ ucfirst($product->type) }} / {{ ucfirst($product->delivery_method) }}</p>


                {{-- Price & Qty --}}
                <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:24px; flex-wrap:wrap; gap:12px;">
                    <div id="mainPriceDisplay" style="font-size:2.4rem; font-weight:900; color:var(--text-main); letter-spacing:-1px;">
                        @php
                            $a   = $first->price_amount ?? $first->price_usd ?? 0;
                            $c   = $first->currency ?? 'USD';
                            $sym = $currencySymbols[$c] ?? ($c.' ');
                            $dec = in_array($c, ['IDR','JPY']) ? 0 : 2;
                        @endphp
                        {{ $sym . number_format($a, $dec) }}
                    </div>
                    <div style="display:flex; align-items:center; gap:8px; color:var(--text-muted); font-size:0.9rem; font-weight:600;">
                        Quantity:
                        <div style="display:flex;align-items:center;gap:0;border:1px solid var(--border-color);border-radius:8px;overflow:hidden;">
                            <button onclick="updateQty(-1)" style="background:var(--bg-surface);border:none;color:var(--text-main);width:34px;height:34px;cursor:pointer;font-size:1.1rem;font-weight:700;transition:.2s;" onmouseover="this.style.background='var(--bg-body)'" onmouseout="this.style.background='var(--bg-surface)'">-</button>
                            <span id="qtyDisplay" style="min-width:34px;text-align:center;font-weight:700;color:var(--text-main);font-size:1rem;">1</span>
                            <button onclick="updateQty(1)"  style="background:var(--bg-surface);border:none;color:var(--text-main);width:34px;height:34px;cursor:pointer;font-size:1.1rem;font-weight:700;transition:.2s;" onmouseover="this.style.background='var(--bg-body)'" onmouseout="this.style.background='var(--bg-surface)'">+</button>
                        </div>
                    </div>
                </div>


                {{-- Variants --}}
                <div style="font-size:0.85rem; font-weight:700; color:var(--text-muted); text-transform:uppercase; letter-spacing:.5px; margin-bottom:12px;">Variants:</div>
                <div style="display:flex; flex-direction:column; gap:10px; margin-bottom:22px;">
                    @foreach($product->variants as $v)
                        @php
                            $isGift = $product->delivery_method === 'gift';
                            $stok = $isGift ? 999999 : $v->vouchers->where('status', 'AVAILABLE')->count();
                            $isOOS = !$isGift && ($stok <= 0);
                            $vAmount = $v->price_amount ?? $v->price_usd ?? 0;
                            $vCur    = $v->currency ?? 'USD';
                            $vSym    = $currencySymbols[$vCur] ?? ($vCur.' ');
                            $vDec    = in_array($vCur, ['IDR','JPY']) ? 0 : 2;
                        @endphp
                        <div class="new-variant-card {{ $v->id === $first->id ? 'selected' : '' }} {{ $isOOS ? 'oos' : '' }}"
                             id="nvc-{{ $v->id }}"
                             onclick="{{ $isOOS ? '' : "pickVariant({$v->id}, {$vAmount}, '{$vCur}', {$stok})" }}"
                             style="padding:16px 20px; border-radius:12px; border:1px solid {{ $v->id === $first->id ? 'var(--primary)' : 'var(--border-color)' }}; background:{{ $v->id === $first->id ? 'rgba(0,170,255,0.06)' : 'var(--bg-surface)' }}; cursor:{{ $isOOS ? 'not-allowed' : 'pointer' }}; transition:all .2s; opacity:{{ $isOOS ? '.45' : '1' }}; display:flex; justify-content:space-between; align-items:center;">
                            <div>
                                <div style="font-weight:700; font-size:1rem; color:{{ $isOOS ? 'var(--text-muted)' : 'var(--text-main)' }}; margin-bottom:4px;">{{ $v->duration }}</div>
                                <div style="font-size:0.78rem; color:{{ $isOOS ? '#ef4444' : '#22c55e' }}; font-weight:600;">
                                    @if($isOOS)
                                        Sold Out
                                    @elseif($isGift)
                                        ∞ Available
                                    @else
                                        Stock: {{ $stok }}
                                    @endif
                                </div>
                            </div>
                            <div style="font-weight:800; font-size:1.1rem; color:{{ $isOOS ? 'var(--text-muted)' : 'var(--text-main)' }};">
                                {{ $isOOS ? 'SOLD OUT' : ($vSym . number_format($vAmount, $vDec)) }}
                            </div>
                        </div>
                    @endforeach
                </div>


                {{-- Action Buttons --}}
                <div style="display:flex; gap:12px;">
                    <button onclick="goToStep2()" id="btnCheckout" style="flex:1; padding:16px; border:none; border-radius:12px; background:linear-gradient(135deg, #7c3aed, #4f46e5); color:#fff; font-weight:800; font-size:1rem; cursor:pointer; transition:.2s; display:flex; align-items:center; justify-content:center; gap:8px;" onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        ⚡ Checkout
                    </button>
                    <button style="padding:16px 24px; border:1px solid var(--border-color); border-radius:12px; background:var(--bg-surface); color:var(--text-muted); font-weight:700; font-size:0.9rem; cursor:pointer; transition:.2s; white-space:nowrap;" onmouseover="this.style.borderColor='var(--text-muted)'" onmouseout="this.style.borderColor='var(--border-color)'">
                        🛒 Add To Cart
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== STEP 2: PAYMENT ==================== --}}
    <div id="step2" style="display:none; max-width:1200px; margin:0 auto; padding:30px 20px; box-sizing:border-box;">

        {{-- Progress Bar --}}
        <div style="display:flex; align-items:center; justify-content:center; gap:0; margin-bottom:40px;">
            <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
                <div id="pb-cart" style="width:110px; height:32px; border-radius:20px; background:var(--primary); color:#fff; font-size:0.75rem; font-weight:800; display:flex; align-items:center; justify-content:center; letter-spacing:1px;">CART</div>
            </div>
            <div style="flex:1; max-width:80px; height:2px; background:var(--primary); margin-bottom:0;"></div>
            <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
                <div id="pb-payment" style="width:110px; height:32px; border-radius:20px; background:var(--primary); color:#fff; font-size:0.75rem; font-weight:800; display:flex; align-items:center; justify-content:center; letter-spacing:1px;">PAYMENT</div>
            </div>
            <div style="flex:1; max-width:80px; height:2px; background:var(--border-color); margin-bottom:0;"></div>
            <div style="display:flex; flex-direction:column; align-items:center; gap:8px;">
                <div style="width:110px; height:32px; border-radius:20px; background:var(--bg-surface); border:1px solid var(--border-color); color:var(--text-muted); font-size:0.75rem; font-weight:800; display:flex; align-items:center; justify-content:center; letter-spacing:1px;">INVOICE</div>
            </div>
        </div>

        <div style="display:grid; grid-template-columns:1fr 380px; gap:32px; align-items:start;">

            {{-- LEFT: Form --}}
            <div style="display:flex; flex-direction:column; gap:28px; position:relative;">

                {{-- Back to Step 1 --}}
                <button onclick="goToStep1()" style="position:absolute; top:-35px; left:0; background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:0.85rem;font-weight:600;display:flex;align-items:center;gap:6px;padding:0;width:fit-content;transition:.2s;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-muted)'">
                    ← Back to Product
                </button>


                {{-- Gift target (shown only for gift products) --}}
                @if($product->delivery_method === 'gift')
                <div>
                    <h2 style="margin:0 0 16px; font-size:1.1rem; font-weight:800; color:var(--text-main);">Username Account <span style="color:#ef4444;">*</span></h2>
                    <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:14px; padding:20px;">
                        <input type="text" id="giftUsernameInput2" class="input-dark" style="padding:12px 16px; width:100%; box-sizing:border-box;" placeholder="Username Account..." required>
                    </div>
                </div>
                @endif


                {{-- Choose Payment Method --}}
                <div>
                    <h2 style="margin:0 0 16px; font-size:1.1rem; font-weight:800; color:var(--text-main);">Choose Payment Method</h2>
                    <div style="display:flex; flex-direction:column; gap:12px;">

                        {{-- QRIS --}}
                        <div class="pm-card" id="pm-QRIS" onclick="selectPaymentNew('QRIS', this)"
                             style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:14px; padding:18px 20px; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:14px;">
                                <div style="width:42px; height:42px; border-radius:10px; background:rgba(0,170,255,.12); display:flex; align-items:center; justify-content:center; font-size:1.4rem;">📱</div>
                                <div>
                                    <div style="font-weight:700; color:var(--text-main); font-size:0.95rem;">QRIS / BCA</div>
                                    <div style="font-size:0.78rem; color:var(--text-muted);">Bank Transfer & QRIS</div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <span class="pm-total-display" style="font-size:0.85rem; color:var(--text-muted); font-weight:600;"></span>
                                <div class="pm-select-btn" style="padding:7px 18px; border-radius:8px; background:var(--bg-body); border:1px solid var(--border-color); color:var(--text-muted); font-weight:700; font-size:0.8rem; transition:.2s;">Select</div>
                            </div>
                        </div>

                        {{-- Stripe --}}
                        <div class="pm-card" id="pm-stripe" onclick="selectPaymentNew('stripe', this)"
                             style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:14px; padding:18px 20px; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:14px;">
                                <div style="width:42px; height:42px; border-radius:10px; background:rgba(99,91,255,.12); display:flex; align-items:center; justify-content:center; font-size:1.4rem;">💳</div>
                                <div>
                                    <div style="font-weight:700; color:var(--text-main); font-size:0.95rem;">Stripe</div>
                                    <div style="font-size:0.78rem; color:var(--text-muted);">Debit / Credit Card</div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <span class="pm-total-display" style="font-size:0.85rem; color:var(--text-muted); font-weight:600;"></span>
                                <div class="pm-select-btn" style="padding:7px 18px; border-radius:8px; background:var(--bg-body); border:1px solid var(--border-color); color:var(--text-muted); font-weight:700; font-size:0.8rem; transition:.2s;">Select</div>
                            </div>
                        </div>

                        {{-- PayPal --}}
                        <div class="pm-card" id="pm-paypal" onclick="selectPaymentNew('paypal', this)"
                             style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:14px; padding:18px 20px; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:14px;">
                                <div style="width:42px; height:42px; border-radius:10px; background:rgba(0,112,243,.12); display:flex; align-items:center; justify-content:center; font-size:1.4rem;">🅿️</div>
                                <div>
                                    <div style="font-weight:700; color:var(--text-main); font-size:0.95rem;">PayPal</div>
                                    <div style="font-size:0.78rem; color:var(--text-muted);">Goods & Services · USD</div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <span class="pm-total-display" style="font-size:0.85rem; color:var(--text-muted); font-weight:600;"></span>
                                <div class="pm-select-btn" style="padding:7px 18px; border-radius:8px; background:var(--bg-body); border:1px solid var(--border-color); color:var(--text-muted); font-weight:700; font-size:0.8rem; transition:.2s;">Select</div>
                            </div>
                        </div>

                        {{-- Crypto --}}
                        <div class="pm-card" id="pm-crypto" onclick="selectPaymentNew('crypto', this)"
                             style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:14px; padding:18px 20px; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:14px;">
                                <div style="width:42px; height:42px; border-radius:10px; background:rgba(247,147,26,.12); display:flex; align-items:center; justify-content:center; font-size:1.4rem;">₿</div>
                                <div>
                                    <div style="font-weight:700; color:var(--text-main); font-size:0.95rem;">Crypto</div>
                                    <div style="font-size:0.78rem; color:var(--text-muted);">BTC · ETH · USDT · +300 coins</div>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:12px;">
                                <span class="pm-total-display" style="font-size:0.85rem; color:var(--text-muted); font-weight:600;"></span>
                                <div class="pm-select-btn" style="padding:7px 18px; border-radius:8px; background:var(--bg-body); border:1px solid var(--border-color); color:var(--text-muted); font-weight:700; font-size:0.8rem; transition:.2s;">Select</div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Terms checkbox --}}
                <div>
                    <label style="display:flex; align-items:flex-start; gap:12px; cursor:pointer;">
                        <input type="checkbox" id="refundAgreeCheck" onchange="syncPayBtn()"
                               style="width:18px; height:18px; margin-top:2px; accent-color:var(--primary); cursor:pointer; flex-shrink:0;">
                        <span id="refundAgree" style="font-size:0.85rem; color:var(--text-muted);">
                            I have read and agree to the applicable <a href="{{ route('tos') }}" target="_blank" style="color:var(--primary); text-decoration:underline; font-weight:700;">Terms &amp; Policy</a>.
                        </span>
                    </label>
                </div>
            </div>

            {{-- RIGHT: Order Summary --}}
            <div style="position:sticky; top:20px;">
                <div style="background:var(--bg-surface); border:1px solid var(--border-color); border-radius:16px; padding:24px; display:flex; flex-direction:column; gap:18px;">
                    <h2 style="margin:0; font-size:1rem; font-weight:800; color:var(--text-main);">Order Summary</h2>

                    {{-- Product row --}}
                    <div style="display:flex; gap:14px; align-items:center; padding-bottom:16px; border-bottom:1px solid var(--border-color);">
                        <div style="width:56px; height:40px; border-radius:8px; overflow:hidden; flex-shrink:0; background:var(--bg-body);">
                            @if($product->image)
                                <img src="{{ asset('storage/' . $product->image) }}" style="width:100%;height:100%;object-fit:cover;">
                            @endif
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:700; color:var(--text-main); font-size:0.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $product->name }}</div>
                            <div id="summaryVariantName" style="font-size:0.78rem; color:var(--text-muted);">
                                @php
                                    $a   = $first->price_amount ?? $first->price_usd ?? 0;
                                    $c   = $first->currency ?? 'USD';
                                    $sym = $currencySymbols[$c] ?? ($c.' ');
                                    $dec = in_array($c, ['IDR','JPY']) ? 0 : 2;
                                @endphp
                                {{ $first->duration }} &mdash;
                                <span style="color:var(--primary); font-weight:700;">{{ $sym . number_format($a, $dec) }}</span>
                            </div>
                        </div>
                        <div style="display:flex; align-items:center; gap:0; border:1px solid var(--border-color); border-radius:8px; overflow:hidden; flex-shrink:0;">
                            <button onclick="updateQty(-1)" style="background:var(--bg-body);border:none;color:var(--text-main);width:28px;height:28px;cursor:pointer;font-weight:700;font-size:0.9rem;transition:.2s;">-</button>
                            <span id="summaryQtyDisplay" style="min-width:28px;text-align:center;font-weight:700;color:var(--text-main);font-size:0.85rem;">1</span>
                            <button onclick="updateQty(1)"  style="background:var(--bg-body);border:none;color:var(--text-main);width:28px;height:28px;cursor:pointer;font-weight:700;font-size:0.9rem;transition:.2s;">+</button>
                        </div>
                    </div>

                    {{-- Coupon in summary --}}
                    <div style="display:flex; gap:8px;">
                        <input type="text" id="promoInput2" class="input-dark" placeholder="Enter coupon code" style="flex:1;padding:10px 14px;font-size:0.85rem;">
                        <button onclick="checkCouponFromSummary()" style="background:var(--bg-body);border:1px solid var(--border-color);color:var(--text-main);padding:10px 14px;border-radius:10px;cursor:pointer;font-size:1rem;transition:.2s;" onmouseover="this.style.borderColor='var(--primary)'" onmouseout="this.style.borderColor='var(--border-color)'">→</button>
                    </div>
                    <div id="promoMessage2" style="font-size:0.8rem; margin-top:-10px; font-weight:600; display:none;"></div>

                    {{-- Price breakdown --}}
                    <div style="display:flex; flex-direction:column; gap:10px; border-top:1px solid var(--border-color); padding-top:16px;">
                        <div style="display:flex; justify-content:space-between; color:var(--text-muted); font-size:0.9rem;">
                            <span>Subtotal</span>
                            <span id="summarySubtotal" style="font-weight:600; color:var(--text-main);"></span>
                        </div>
                        <div style="display:flex; justify-content:space-between; color:var(--text-muted); font-size:0.9rem;">
                            <span>Payment Method Fee</span>
                            <span style="font-weight:600; color:var(--text-main);">$0.00</span>
                        </div>
                        <div style="display:flex; justify-content:space-between; color:var(--text-main); font-size:0.95rem; font-weight:800;">
                            <span>Total</span>
                            <span id="summaryTotal"></span>
                        </div>
                    </div>

                    {{-- Proceed to Payment --}}
                    <button onclick="handlePayment()" id="btnPay"
                            style="width:100%; padding:16px; border:none; border-radius:12px; background:linear-gradient(135deg, #7c3aed, #4f46e5); color:#fff; font-weight:800; font-size:1rem; cursor:pointer; transition:.2s; display:flex; align-items:center; justify-content:center; gap:8px;"
                            onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'"
                            {{ ($product->delivery_method !== 'gift' && $first_stock <= 0) ? 'disabled' : '' }}>
                        Proceed to Payment →
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Hidden form for submission --}}
    <form action="{{ route('payment.process') }}" method="POST" id="checkoutForm" style="display:none;">
        @csrf
        <input type="hidden" name="variant_id"      id="variantInput"       value="{{ $first->id }}">
        <input type="hidden" name="quantity"         id="quantityInput"      value="1">
        <input type="hidden" name="payment_method"   id="paymentMethodInput" value="">
        <input type="hidden" name="promo_code"       id="appliedPromoInput"  value="">
        <input type="hidden" name="currency"         id="currencyInput"      value="{{ $first->currency ?? 'USD' }}">
        @if($product->delivery_method === 'gift')
        <input type="hidden" name="gift_username" id="giftUsernameHidden" value="">
        @endif
    </form>

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


    @php
        // Moved to the top of the file to prevent 500 Undefined variable $first error
    @endphp

    <script>
        const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};

        // ────────────────────────────────────────────────
        // GLOBAL STATE
        // ────────────────────────────────────────────────
        let activePrice    = {{ $first->price_amount ?? $first->price_usd ?? 0 }};
        let activeCurrency = '{{ $first->currency ?? "USD" }}';
        let activeSym      = '{{ ["USD"=>"$","IDR"=>"Rp ","EUR"=>"€","GBP"=>"£","MYR"=>"RM ","SGD"=>"S$","THB"=>"฿","JPY"=>"¥","AUD"=>"A$"][$first->currency ?? "USD"] ?? "$ " }}';
        let activeDec      = {{ in_array($first->currency ?? 'USD', ['IDR','JPY']) ? 0 : 2 }};
        let quantity       = 1;
        let currentStock   = {{ $product->delivery_method === 'gift' ? 999999 : ($first_stock ?? 0) }};
        let activePromo    = { type: null, value: 0, max_discount: 0 };
        let activeVariantId= {{ $first->id ?? 0 }};
        let activeVariantDuration = '{{ $first->duration ?? "" }}';
        let currentLang    = 'en';
        let selectedPaymentMethod = '';

        const currencySymbols = {
            'USD':'$','IDR':'Rp ','EUR':'€','GBP':'£',
            'MYR':'RM ','SGD':'S$','THB':'฿','JPY':'¥','AUD':'A$'
        };

        // ────────────────────────────────────────────────
        // STEP NAVIGATION
        // ────────────────────────────────────────────────
        function goToStep2() {
            if (!isLoggedIn) {
                document.getElementById('loginModal').style.display = 'flex';
                return;
            }

            // Sync promo from step1 input to step2 input
            const p1 = document.getElementById('promoInput');
            const p2 = document.getElementById('promoInput2');
            if (p1 && p2 && p1.value) p2.value = p1.value;

            // Update order summary
            updateSummary();

            const s1 = document.getElementById('step1');
            const s2 = document.getElementById('step2');
            s1.style.transition = 'opacity .3s ease';
            s1.style.opacity = '0';
            setTimeout(() => {
                s1.style.display = 'none';
                s2.style.display = 'block';
                s2.style.opacity = '0';
                s2.style.transition = 'opacity .3s ease';
                requestAnimationFrame(() => requestAnimationFrame(() => s2.style.opacity = '1'));
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 280);

            // Disable pay button until TOS checked
            syncPayBtn();
        }

        function goToStep1() {
            const s1 = document.getElementById('step1');
            const s2 = document.getElementById('step2');
            s2.style.transition = 'opacity .3s ease';
            s2.style.opacity = '0';
            setTimeout(() => {
                s2.style.display = 'none';
                s1.style.display = 'block';
                s1.style.opacity = '0';
                s1.style.transition = 'opacity .3s ease';
                requestAnimationFrame(() => requestAnimationFrame(() => s1.style.opacity = '1'));
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }, 280);
        }

        // ────────────────────────────────────────────────
        // VARIANT SELECTION
        // ────────────────────────────────────────────────
        function pickVariant(id, amount, currency, stock) {
            // Remove selected state from all cards
            document.querySelectorAll('.new-variant-card:not(.oos)').forEach(c => {
                c.style.border = '1px solid var(--border-color)';
                c.style.background = 'var(--bg-surface)';
            });
            // Highlight selected
            const card = document.getElementById('nvc-' + id);
            if (card) {
                card.style.border = '1px solid var(--primary)';
                card.style.background = 'rgba(0,170,255,0.06)';
            }

            activeVariantId   = id;
            activePrice       = parseFloat(amount) || 0;
            activeCurrency    = currency || 'USD';
            activeSym         = currencySymbols[currency] || '$';
            activeDec         = ['IDR','JPY'].includes(currency) ? 0 : 2;
            currentStock      = parseInt(stock) || 0;
            activeVariantDuration = card ? card.querySelector('div > div')?.innerText || '' : '';
            quantity = 1;

            document.getElementById('variantInput').value  = id;
            document.getElementById('currencyInput').value = currency;
            if (document.getElementById('qtyDisplay'))    document.getElementById('qtyDisplay').innerText = 1;
            if (document.getElementById('summaryQtyDisplay')) document.getElementById('summaryQtyDisplay').innerText = 1;
            document.getElementById('quantityInput').value = 1;

            // Update price display
            calculateTotal();
            updateSummary();
        }

        // ────────────────────────────────────────────────
        // QUANTITY
        // ────────────────────────────────────────────────
        function updateQty(change) {
            if (currentStock <= 0) return;
            let n = quantity + change;
            if (n < 1) n = 1;
            if (n > currentStock) { alert('Maximum stock reached!'); n = currentStock; }
            quantity = n;
            document.getElementById('quantityInput').value = n;
            if (document.getElementById('qtyDisplay'))        document.getElementById('qtyDisplay').innerText = n;
            if (document.getElementById('summaryQtyDisplay')) document.getElementById('summaryQtyDisplay').innerText = n;
            calculateTotal();
            updateSummary();
        }

        // ────────────────────────────────────────────────
        // PRICE CALCULATION
        // ────────────────────────────────────────────────
        function fmtPrice(amount) {
            return activeSym + parseFloat(amount).toFixed(activeDec);
        }

        function calculateTotal() {
            let base     = activePrice;
            let subtotal = base * quantity;
            let disc     = 0;
            if (activePromo.type === 'percent') {
                disc = subtotal * (activePromo.value / 100);
                if (activePromo.max_discount > 0) {
                    let maxDiscNative = activeCurrency === 'IDR' ? activePromo.max_discount : activePromo.max_discount / 15500;
                    if (disc > maxDiscNative) disc = maxDiscNative;
                }
            } else if (activePromo.type === 'fixed') {
                disc = activeCurrency === 'IDR' ? activePromo.value : activePromo.value / 15500;
            }
            let final = Math.max(0, subtotal - disc);
            const el = document.getElementById('mainPriceDisplay');
            if (el) el.innerText = fmtPrice(final);
            return final;
        }

        function updateSummary() {
            const final = calculateTotal();
            const subtotal = activePrice * quantity;

            const sVariant  = document.getElementById('summaryVariantName');
            const sSubtotal = document.getElementById('summarySubtotal');
            const sTotal    = document.getElementById('summaryTotal');
            const pmDisplays = document.querySelectorAll('.pm-total-display');

            if (sVariant)  sVariant.innerHTML  = activeVariantDuration + ' &mdash; <span style="color:var(--primary);font-weight:700;">' + fmtPrice(activePrice) + '</span>';
            if (sSubtotal) sSubtotal.innerText  = fmtPrice(subtotal);
            if (sTotal)    sTotal.innerText      = fmtPrice(final);
            pmDisplays.forEach(el => el.innerText = 'Total ' + fmtPrice(final));
        }

        // ────────────────────────────────────────────────
        // COUPON
        // ────────────────────────────────────────────────
        async function checkCoupon() {
            const code = document.getElementById('promoInput').value;
            const msg  = document.getElementById('promoMessage');
            if (!code) return;
            msg.innerText = 'Checking...'; msg.style.color = 'var(--text-muted)';
            await _applyPromo(code, msg);
            // sync to step2 field
            if (document.getElementById('promoInput2')) document.getElementById('promoInput2').value = code;
        }

        async function checkCouponFromSummary() {
            const code = document.getElementById('promoInput2').value;
            const msg  = document.getElementById('promoMessage2');
            if (!code) return;
            msg.style.display = 'block';
            msg.innerText = 'Checking...'; msg.style.color = 'var(--text-muted)';
            await _applyPromo(code, msg);
            // sync back to step1 field
            if (document.getElementById('promoInput')) document.getElementById('promoInput').value = code;
        }

        async function _applyPromo(code, msgEl) {
            try {
                const res = await fetch("{{ route('payment.check_promo') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    body: JSON.stringify({ promo_code: code, variant_id: activeVariantId, quantity: quantity, lang: currentLang })
                });
                const data = await res.json();
                if (data.success) {
                    msgEl.style.color = 'var(--success)';
                    msgEl.innerText = '✅ Promo Code Applied!';
                    activePromo = { type: data.type, value: parseFloat(data.value), max_discount: parseFloat(data.max_discount || 0) };
                    document.getElementById('appliedPromoInput').value = code;
                } else {
                    msgEl.style.color = 'var(--danger)';
                    msgEl.innerText = '❌ ' + (data.message || 'Invalid Code');
                    activePromo = { type: null, value: 0, max_discount: 0 };
                    document.getElementById('appliedPromoInput').value = '';
                }
                calculateTotal();
                updateSummary();
            } catch(e) { console.error(e); }
        }

        // ────────────────────────────────────────────────
        // PAYMENT METHOD
        // ────────────────────────────────────────────────
        function selectPaymentNew(method, el) {
            document.querySelectorAll('.pm-card').forEach(c => {
                c.style.borderColor = 'var(--border-color)';
                c.style.background  = 'var(--bg-surface)';
                const btn = c.querySelector('.pm-select-btn');
                if (btn) { btn.style.background = 'var(--bg-body)'; btn.style.color = 'var(--text-muted)'; }
            });
            el.style.borderColor = 'var(--primary)';
            el.style.background  = 'rgba(0,170,255,0.04)';
            const btn = el.querySelector('.pm-select-btn');
            if (btn) { btn.style.background = 'var(--primary)'; btn.style.color = '#fff'; }
            selectedPaymentMethod = method;
            document.getElementById('paymentMethodInput').value = method;
        }

        // ────────────────────────────────────────────────
        // TOS CHECKBOX SYNC
        // ────────────────────────────────────────────────
        function syncPayBtn() {
            const check = document.getElementById('refundAgreeCheck');
            const btn   = document.getElementById('btnPay');
            if (!btn) return;
            const outOfStock = {{ ($product->delivery_method !== 'gift' && $first_stock <= 0) ? 'true' : 'false' }};
            if (outOfStock) return;
            const ok = check && check.checked;
            btn.disabled       = !ok;
            btn.style.opacity  = ok ? '1' : '0.5';
            btn.style.cursor   = ok ? 'pointer' : 'not-allowed';
        }

        // ────────────────────────────────────────────────
        // PAYMENT SUBMISSION
        // ────────────────────────────────────────────────
        function handlePayment() {
            if (!isLoggedIn) {
                document.getElementById('loginModal').style.display = 'flex';
                return;
            }

            const refundCheck = document.getElementById('refundAgreeCheck');
            if (refundCheck && !refundCheck.checked) {
                alert('Please agree to the Terms & Policy before proceeding!');
                refundCheck.style.animation = 'shake .4s ease';
                setTimeout(() => refundCheck.style.animation = '', 400);
                return;
            }

            if (!selectedPaymentMethod) {
                alert('Please select a payment method first!');
                return;
            }

            @if($product->delivery_method === 'gift')
            // Prefer step2 input if populated, otherwise step1
            const g2 = document.getElementById('giftUsernameInput2');
            const g1 = document.getElementById('giftUsernameInput');
            const giftVal = (g2 && g2.value.trim()) ? g2.value.trim() : (g1 && g1.value.trim() ? g1.value.trim() : '');
            if (!giftVal) { alert('Please enter the username account.'); return; }
            document.getElementById('giftUsernameHidden').value = giftVal;
            @endif

            const btn = document.getElementById('btnPay');
            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.innerText = 'Processing...';

            document.getElementById('checkoutForm').submit();
        }

        // ────────────────────────────────────────────────
        // SLIDER / CAROUSEL
        // ────────────────────────────────────────────────
        let slideIndex = 0;
        const track = document.getElementById('sliderTrack');
        const thumbs = document.querySelectorAll('.thumb-item');
        const slideCount = {{ $totalSlides }};

        function updateSlider() {
            if (track) track.style.transform = `translateX(-${slideIndex * 100}%)`;
            thumbs.forEach((t, i) => {
                t.style.border = i === slideIndex ? '2px solid var(--primary)' : '2px solid transparent';
            });
        }
        function moveSlide(n) {
            slideIndex += n;
            if (slideIndex >= slideCount) slideIndex = 0;
            if (slideIndex < 0) slideIndex = slideCount - 1;
            updateSlider();
        }
        function currentSlide(n) { slideIndex = n; updateSlider(); }

        // ────────────────────────────────────────────────
        // LIGHTBOX
        // ────────────────────────────────────────────────
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
            if (event.target.id === 'lightboxModal') document.getElementById('lightboxModal').style.display = 'none';
        }

        // ────────────────────────────────────────────────
        // MODAL + LANGUAGE
        // ────────────────────────────────────────────────
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }
        function toggleLang()   { document.getElementById('langOptions').classList.toggle('show'); }
        window.onclick = function(event) {
            if (!event.target.closest('.lang-wrapper')) document.getElementById('langOptions').classList.remove('show');
        };

        const translations = {
            'id': { stock:'Stok', unit:'Unit', promo_placeholder:'Kode Promo', back:'KEMBALI' },
            'en': { stock:'Stock', unit:'Unit', promo_placeholder:'Promo Code', back:'BACK' }
        };

        function selectLang(lang, text, flagUrl) {
            document.getElementById('currentFlag').src = flagUrl;
            document.getElementById('currentLangText').innerText = text;
            document.getElementById('langOptions').classList.remove('show');
            localStorage.setItem('abuser_lang', lang);
            currentLang = lang;
        }

        // ────────────────────────────────────────────────
        // DARK MODE
        // ────────────────────────────────────────────────
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

        // ────────────────────────────────────────────────
        // INIT
        // ────────────────────────────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            const savedLang = localStorage.getItem('abuser_lang') || 'en';
            const flag = savedLang === 'id' ? 'https://flagcdn.com/w20/id.png' : 'https://flagcdn.com/w20/us.png';
            document.getElementById('currentFlag').src = flag;
            document.getElementById('currentLangText').innerText = savedLang.toUpperCase();
            currentLang = savedLang;

            updateThemeIcon();
            calculateTotal();
            updateSummary();
            syncPayBtn();
        });
    </script>



    <div class="back-wrapper anim-fade delay-1">
        <a href="/" class="back-link-minimal" style="color: var(--text-muted);">
            <div style="width: 24px; height: 24px; flex-shrink: 0; display: flex; align-items: center;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width: 100%; height: 100%;">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
            </div>
            <span class="lang-back">BACK</span>
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
                <h3 class="lang-desc">Product Description</h3>
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

                    @if($product->delivery_method === 'gift')
                    <div class="stock-badge" style="background: rgba(0, 170, 255, 0.1); color: #00aaff; border-color: rgba(0, 170, 255, 0.2);">
                        <span>Delivery Method</span>: <span style="font-weight: 700;">Via Gift to Account</span>
                    </div>
                    @else
                    <div class="stock-badge">
                        <span class="lang-stock">Stok</span>: <span id="stockDisplay">{{ $first_stock }}</span> <span id="unitDisplay" class="lang-unit">@if($first_stock == 1) Unit @else Units @endif</span>
                    </div>
                    @endif
                </div>
            </div>

            <label class="form-label lang-step1">1. Select Duration</label>
            <div class="variant-grid">
                @foreach($product->variants as $v)
                    @php
                        $isGift = $product->delivery_method === 'gift';
                        $stok = $isGift ? 999999 : $v->vouchers->where('status', 'AVAILABLE')->count();
                        $isOutOfStock = !$isGift && ($stok <= 0);
                    @endphp

                    <div class="variant-item {{ ($first->id == $v->id) ? 'active' : '' }} {{ $isOutOfStock ? 'disabled' : '' }}"
                         onclick="{{ $isOutOfStock ? '' : "selectVariant(this, '{$v->id}', '" . ($v->price_amount ?? $v->price_usd) . "', '{$stok}', '" . ($v->currency ?? 'USD') . "')" }}">
                        <div>
                            <div style="font-weight:700; font-size:1rem; color:var(--text-main);" class="lang-duration" data-val="{{ $v->duration }}">{{ $v->duration }}</div>
                            @if(!$isGift)
                            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">
                                <span class="lang-stock">Stock</span>: <span class="stock-num">{{ $stok }}</span> <span class="lang-unit">Unit</span>
                            </div>
                            @else
                            <div style="font-size:0.75rem; color:var(--text-muted); margin-top:2px;">
                                <span class="lang-stock">Stock</span>: ∞
                            </div>
                            @endif
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

            @if($product->delivery_method === 'gift')
                <label class="form-label">2. Target Account & Promo</label>
                <div id="giftUsernameSection" style="margin-bottom: 16px;">
                    <input type="text" name="gift_username" id="giftUsernameInput" class="input-dark" style="width: 100%; box-sizing: border-box; text-align: left; padding: 12px 16px;" placeholder="Username Forum / Account Target..." required>
                </div>
            @else
                <label class="form-label lang-step2">2. Qty & Promo</label>
            @endif

            <div class="control-row">
                <div>
                    <div class="promo-wrapper">
                        <input type="text" id="promoInput" class="input-dark" placeholder="Kode Promo">
                        <button type="button" onclick="checkCoupon()" class="btn-apply">APPLY</button>
                    </div>
                    <div id="promoMessage" style="font-size:0.8rem; margin-top:5px; font-weight:bold;"></div>
                </div>

                @if($product->delivery_method !== 'gift')
                <div class="qty-box">
                    <button type="button" class="qty-btn" onclick="updateQty(-1)">-</button>
                    <span id="qtyDisplay" style="font-weight:700; color:var(--text-main);">1</span>
                    <button type="button" class="qty-btn" onclick="updateQty(1)">+</button>
                </div>
                @endif
            </div>

            <label class="form-label lang-step3">3. Payment Method</label>
            <div class="pm-grid">
                <div class="pm-item" onclick="selectPayment('QRIS', this)">📱 QRIS / BCA</div>
                <div class="pm-item" onclick="selectPayment('stripe', this)">💳 STRIPE</div>
                <div class="pm-item" onclick="selectPayment('paypal', this)">🅿️ PAYPAL</div>
                <div class="pm-item" onclick="selectPayment('crypto', this)">₿ CRYPTO</div>
            </div>

            <!-- ══ REFUND POLICY & TERMS ══ -->
            <div id="refundPolicyBox" style="margin-bottom: 16px;">
                <label style="display:flex; align-items:center; gap:12px; cursor:pointer; padding: 12px 0;">
                    <input type="checkbox" id="refundAgreeCheck" onchange="togglePayBtn()"
                           style="width:18px; height:18px; accent-color:var(--primary); cursor:pointer; flex-shrink:0;">
                    <span id="refundAgree" style="font-size:0.85rem; color:var(--text-muted); font-weight:500;">
                        I have read and agree to the applicable <a href="{{ route('tos') }}" target="_blank" style="color: var(--primary); font-weight: 700; text-decoration: underline;">Terms & Policy</a>.
                    </span>
                </label>
            </div>

            <button type="button" class="btn-pay" id="btnPay"
                {{ ($product->delivery_method !== 'gift' && $first_stock <= 0) ? 'disabled' : '' }}
                onclick="handlePayment()">
                PAY NOW
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

            // Refund agreement check
            const refundCheck = document.getElementById('refundAgreeCheck');
            if (refundCheck && !refundCheck.checked) {
                alert(translations[currentLang].refund_alert);
                refundCheck.parentElement.style.animation = 'shake .4s ease';
                setTimeout(() => refundCheck.parentElement.style.animation = '', 400);
                return false;
            }

            @if($product->delivery_method === 'gift')
            const giftUsername = document.getElementById('giftUsernameInput').value.trim();
            if (!giftUsername) {
                alert(currentLang === 'id' ? 'Silakan masukkan username target!' : 'Please enter target username!');
                return false;
            }
            @endif

            const paymentMethod = document.getElementById('paymentMethodInput').value;
            if (!paymentMethod) {
                const t = translations[currentLang];
                alert(t.no_pm);
                return false;
            }

            // Prevent Double Click
            const btn = document.getElementById('btnPay');
            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.style.cursor = 'not-allowed';
            btn.innerText = 'MEMPROSES...';

            document.getElementById('checkoutForm').submit();
        }

        function togglePayBtn() {
            const check = document.getElementById('refundAgreeCheck');
            const btn   = document.getElementById('btnPay');
            const outOfStock = {{ ($product->delivery_method !== 'gift' && $first_stock <= 0) ? 'true' : 'false' }};
            if (outOfStock) return; // never re-enable if sold out
            btn.disabled = !check.checked;
            btn.style.opacity = check.checked ? '1' : '0.5';
            btn.style.cursor  = check.checked ? 'pointer' : 'not-allowed';
        }

        // Initialise btn as disabled until checkbox ticked
        document.addEventListener('DOMContentLoaded', () => {
            const outOfStock = {{ ($product->delivery_method !== 'gift' && $first_stock <= 0) ? 'true' : 'false' }};
            if (!outOfStock) {
                const btn = document.getElementById('btnPay');
                btn.disabled = true;
                btn.style.opacity = '0.5';
                btn.style.cursor  = 'not-allowed';
            }
        });

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
        let currentStock = {{ $product->delivery_method === 'gift' ? 999999 : ($first_stock ?? 0) }};
        let activePromo = { type: null, value: 0, max_discount: 0 };
        let currentLang = 'en';

        function toggleLang() { document.getElementById('langOptions').classList.toggle('show'); }
        window.onclick = function(event) {
            if (!event.target.closest('.lang-wrapper')) document.getElementById('langOptions').classList.remove('show');
        }

        const translations = {
            'id': { desc: 'Deskripsi Produk', step1: '1. Pilih Durasi Paket', step2: '2. Jumlah & Promo', step3: '3. Metode Pembayaran', stock: 'Stok', unit: 'Unit', pay: 'BAYAR SEKARANG', sold: 'HABIS', promo_check: 'Mengecek...', promo_success: 'Kode Berhasil Dipasang!', promo_invalid: 'Kode Tidak Valid', promo_placeholder: 'Kode Promo', no_pm: 'Silakan pilih metode pembayaran terlebih dahulu!', back: 'KEMBALI', max_stock: 'Stok maksimal tercapai!', refund_agree: 'Saya telah membaca dan menyetujui <a href="/terms" target="_blank" style="color: var(--primary); font-weight: 700; text-decoration: underline;">Terms & Policy</a> yang berlaku.', refund_alert: 'Mohon centang persetujuan Terms & Policy terlebih dahulu!' },
            'en': { desc: 'Product Description', step1: '1. Select Duration', step2: '2. Qty & Promo', step3: '3. Payment Method', stock: 'Stock', unit: 'Unit', pay: 'PAY NOW', sold: 'SOLD OUT', promo_check: 'Checking...', promo_success: 'Promo Code Applied!', promo_invalid: 'Invalid Code', promo_placeholder: 'Promo Code', no_pm: 'Please select a payment method first!', back: 'BACK', max_stock: 'Maximum stock reached!', refund_agree: 'I have read and agree to the applicable <a href="/terms" target="_blank" style="color: var(--primary); font-weight: 700; text-decoration: underline;">Terms & Policy</a>.', refund_alert: 'Please agree to the Terms & Policy before proceeding!' }
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

            const setIfExist = (selector, text) => {
                const el = document.querySelector(selector);
                if (el) el.innerText = text;
            };

            setIfExist('.lang-desc', t.desc);
            setIfExist('.lang-step1', t.step1);
            setIfExist('.lang-step2', t.step2);
            setIfExist('.lang-step3', t.step3);
            setIfExist('.lang-back', t.back);

            document.querySelectorAll('.lang-stock').forEach(el => el.innerText = t.stock);


            const setIfExistHtml = (selector, html) => {
                const el = document.querySelector(selector);
                if (el) el.innerHTML = html;
            };

            setIfExistHtml('#refundAgree', t.refund_agree);

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

    {{-- ==================== ANNOUNCEMENT POPUP ==================== --}}
    <style>
        /* === OVERLAY: centered, no heavy blur for performance === */
        #annPopupOverlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.72);
            z-index: 99998;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        #annPopupOverlay.ann-visible { display: flex; }

        /* === POPUP BOX === */
        #annPopupBox {
            background: linear-gradient(145deg, #1a1d2e 0%, #13151f 100%);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 20px;
            width: 100%;
            max-width: 560px;
            max-height: 85vh;
            overflow-y: auto;
            overflow-x: hidden;
            box-shadow: 0 32px 80px rgba(0,0,0,0.6), 0 0 0 1px rgba(255,255,255,0.04);
            position: relative;
            transform: scale(0.88) translateY(24px);
            opacity: 0;
            transition: transform 0.4s cubic-bezier(0.22,1,0.36,1), opacity 0.35s ease;
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.1) transparent;
        }
        #annPopupBox.ann-enter {
            transform: scale(1) translateY(0);
            opacity: 1;
        }

        /* Accent top bar */
        #annPopupBox::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
            background: linear-gradient(90deg, #2563eb, #7c3aed, #2563eb);
            background-size: 200% 100%;
            border-radius: 20px 20px 0 0;
            animation: annGradShift 3s linear infinite;
        }
        @keyframes annGradShift {
            0%   { background-position: 0% 0%; }
            100% { background-position: 200% 0%; }
        }

        /* Style variants */
        #annPopupBox.style-warning::before { background: linear-gradient(90deg, #d97706, #f59e0b, #d97706); background-size:200% 100%; }
        #annPopupBox.style-success::before { background: linear-gradient(90deg, #059669, #10b981, #059669); background-size:200% 100%; }
        #annPopupBox.style-promo::before   { background: linear-gradient(90deg, #7c3aed, #a855f7, #7c3aed); background-size:200% 100%; }

        /* === POPUP HEADER === */
        .ann-header-wrap {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 22px 24px 0;
            gap: 12px;
        }
        .ann-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
            min-width: 0;
        }
        .ann-icon-badge {
            width: 38px; height: 38px;
            border-radius: 10px;
            background: rgba(37, 99, 235, 0.18);
            border: 1px solid rgba(37, 99, 235, 0.25);
            display: flex; align-items: center; justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }
        #annPopupBox.style-warning .ann-icon-badge { background: rgba(217,119,6,.18); border-color: rgba(217,119,6,.3); }
        #annPopupBox.style-success .ann-icon-badge { background: rgba(5,150,105,.18); border-color: rgba(5,150,105,.3); }
        #annPopupBox.style-promo   .ann-icon-badge { background: rgba(124,58,237,.18); border-color: rgba(124,58,237,.3); }

        .ann-title-text {
            font-size: 0.95rem;
            font-weight: 700;
            color: #f1f5f9;
            line-height: 1.3;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .ann-label-badge {
            font-size: 0.6rem;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
            color: #2563eb;
            background: rgba(37,99,235,0.12);
            padding: 2px 7px;
            border-radius: 20px;
            white-space: nowrap;
        }
        #annPopupBox.style-warning .ann-label-badge { color:#d97706; background:rgba(217,119,6,.12); }
        #annPopupBox.style-success .ann-label-badge { color:#059669; background:rgba(5,150,105,.12); }
        #annPopupBox.style-promo   .ann-label-badge { color:#a855f7; background:rgba(168,85,247,.12); }

        .ann-close-x {
            width: 32px; height: 32px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.08);
            background: transparent;
            color: #64748b;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.95rem;
            transition: background .2s, color .2s, transform .15s;
            flex-shrink: 0;
        }
        .ann-close-x:hover { background: rgba(255,255,255,0.07); color: #f1f5f9; transform: rotate(90deg); }

        /* === POPUP BODY === */
        .ann-content-wrap {
            padding: 18px 24px 20px;
            font-size: 0.875rem;
            line-height: 1.75;
            color: #94a3b8;
        }
        .ann-content-wrap p { margin: 0 0 10px; }
        .ann-content-wrap a { color: #60a5fa; text-decoration: underline; }
        .ann-content-wrap strong, .ann-content-wrap b { color: #e2e8f0; }
        .ann-content-wrap ul, .ann-content-wrap ol { margin: 8px 0 8px 18px; }

        /* === FOOTER NAV === */
        .ann-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 14px 24px 20px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }
        .ann-dots-row { display: flex; gap: 6px; align-items: center; }
        .ann-dot-new {
            width: 7px; height: 7px;
            border-radius: 50%;
            background: rgba(255,255,255,0.18);
            cursor: pointer;
            transition: width .25s, background .25s, border-radius .25s;
        }
        .ann-dot-new.active {
            width: 22px;
            border-radius: 4px;
            background: #2563eb;
        }
        #annPopupBox.style-warning .ann-dot-new.active { background: #d97706; }
        #annPopupBox.style-success .ann-dot-new.active { background: #059669; }
        #annPopupBox.style-promo   .ann-dot-new.active { background: #a855f7; }

        .ann-btns-row { display: flex; gap: 8px; }
        .ann-btn-ghost {
            padding: 7px 16px;
            border-radius: 8px;
            border: 1px solid rgba(255,255,255,0.1);
            background: transparent;
            color: #64748b;
            cursor: pointer;
            font-size: 0.78rem;
            font-weight: 600;
            transition: .2s;
        }
        .ann-btn-ghost:hover { background: rgba(255,255,255,0.06); color: #e2e8f0; }
        .ann-btn-primary {
            padding: 7px 18px;
            border-radius: 8px;
            border: none;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            cursor: pointer;
            font-size: 0.78rem;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(37,99,235,0.3);
            transition: .2s;
        }
        .ann-btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 18px rgba(37,99,235,0.45); }
        #annPopupBox.style-warning .ann-btn-primary { background: linear-gradient(135deg,#d97706,#b45309); box-shadow:0 4px 12px rgba(217,119,6,.3); }
        #annPopupBox.style-success .ann-btn-primary { background: linear-gradient(135deg,#059669,#047857); box-shadow:0 4px 12px rgba(5,150,105,.3); }
        #annPopupBox.style-promo   .ann-btn-primary { background: linear-gradient(135deg,#7c3aed,#6d28d9); box-shadow:0 4px 12px rgba(124,58,237,.3); }
    </style>

    <!-- Overlay -->
    <div id="annPopupOverlay">
        <div id="annPopupBox">
            <!-- Header -->
            <div class="ann-header-wrap">
                <div class="ann-header-left">
                    <div class="ann-icon-badge" id="annPopupIcon">📢</div>
                    <div style="min-width:0;">
                        <div class="ann-title-text" id="annPopupTitle">Announcement</div>
                        <div class="ann-label-badge" id="annStyleLabel">ANNOUNCEMENT</div>
                    </div>
                </div>
                <button class="ann-close-x" onclick="dismissCurrentAnn()" title="Close">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Body -->
            <div class="ann-content-wrap" id="annPopupBody"></div>

            <!-- Footer nav (multi-announcement) -->
            <div class="ann-footer" id="annPopupNav" style="display:none;">
                <div class="ann-dots-row" id="annDots"></div>
                <div class="ann-btns-row">
                    <button class="ann-btn-ghost" id="annPrevBtn">← Prev</button>
                    <button class="ann-btn-primary" id="annNextBtn">Next →</button>
                </div>
            </div>

            <!-- Single ann footer -->
            <div id="annSingleFooter" style="padding:0 24px 20px; display:flex; justify-content:flex-end;">
                <button class="ann-btn-primary" onclick="dismissCurrentAnn()">Got it ✓</button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        const DISMISS_KEY = 'abuser_dismissed_anns';

        function getDismissed() {
            try { return JSON.parse(localStorage.getItem(DISMISS_KEY) || '[]'); } catch { return []; }
        }
        function addDismissed(id) {
            const d = getDismissed();
            if (!d.includes(id)) d.push(id);
            localStorage.setItem(DISMISS_KEY, JSON.stringify(d));
        }

        let allAnns = [], allAnnsOriginal = [], currentAnnIdx = 0;

        const STYLE_LABELS = { default:'ANNOUNCEMENT', warning:'WARNING', success:'NOTICE', promo:'PROMO 🎁' };
        const STYLE_ICONS  = { default:'📢', warning:'⚠️', success:'✅', promo:'🎁' };

        function loadAnnouncements() {
            fetch('/api/announcements/active')
                .then(r => r.json())
                .then(data => {
                    allAnnsOriginal = data;
                    const dismissed = getDismissed();
                    allAnns = data.filter(a => !dismissed.includes(a.id));
                    if (allAnns.length > 0) {
                        renderDots();
                        setTimeout(() => showAnn(0), 400); // slight delay after page load
                    }
                    // Show bell if there are any announcements (even dismissed ones)
                    if (data.length > 0) showBell();
                })
                .catch(() => {});
        }

        function showBell() {
            const btn = document.getElementById('annBellBtn');
            if (btn) btn.style.display = 'flex';

            const dot = document.getElementById('annBellDot');
            if (dot) {
                // Show dot only if there are unread announcements
                if (allAnns.length > 0) {
                    dot.style.display = 'block';
                } else {
                    dot.style.display = 'none';
                }
            }
        }

        function renderDots() {
            const wrap = document.getElementById('annDots');
            wrap.innerHTML = '';
            allAnns.forEach((_, i) => {
                const d = document.createElement('div');
                d.className = 'ann-dot-new' + (i === 0 ? ' active' : '');
                d.onclick = () => showAnn(i);
                wrap.appendChild(d);
            });
            const isMulti = allAnns.length > 1;
            document.getElementById('annPopupNav').style.display    = isMulti ? 'flex' : 'none';
            document.getElementById('annSingleFooter').style.display = isMulti ? 'none' : 'flex';
        }

        window.showAnn = function(idx) {
            if (idx < 0 || idx >= allAnns.length) return;
            currentAnnIdx = idx;
            const ann = allAnns[idx];
            const box = document.getElementById('annPopupBox');

            // Apply style class
            box.className = '';
            if (ann.popup_style && ann.popup_style !== 'default') {
                box.classList.add('style-' + ann.popup_style);
            }

            document.getElementById('annPopupIcon').textContent  = STYLE_ICONS[ann.popup_style]  || '📢';
            document.getElementById('annStyleLabel').textContent  = STYLE_LABELS[ann.popup_style] || 'ANNOUNCEMENT';
            document.getElementById('annPopupTitle').textContent  = ann.title;
            document.getElementById('annPopupBody').innerHTML     = ann.content;

            // Dots
            document.querySelectorAll('.ann-dot-new').forEach((d, i) => d.classList.toggle('active', i === idx));

            // Multi nav buttons
            const prevBtn = document.getElementById('annPrevBtn');
            const nextBtn = document.getElementById('annNextBtn');
            if (prevBtn) {
                prevBtn.disabled = idx === 0;
                prevBtn.style.opacity = idx === 0 ? '0.4' : '1';
                prevBtn.onclick = () => showAnn(currentAnnIdx - 1);
            }
            if (nextBtn) {
                if (idx === allAnns.length - 1) {
                    nextBtn.textContent = 'Done ✓';
                    nextBtn.onclick = () => dismissCurrentAnn();
                } else {
                    nextBtn.textContent = 'Next →';
                    nextBtn.onclick = () => showAnn(currentAnnIdx + 1);
                }
            }

            // Show overlay + animate box in
            const overlay = document.getElementById('annPopupOverlay');
            overlay.classList.add('ann-visible');
            document.body.style.overflow = 'hidden';

            // Trigger enter animation on next frame
            requestAnimationFrame(() => requestAnimationFrame(() => box.classList.add('ann-enter')));
        };

        function hideOverlay() {
            const overlay = document.getElementById('annPopupOverlay');
            const box = document.getElementById('annPopupBox');
            box.classList.remove('ann-enter'); // triggers exit (scale down)
            setTimeout(() => {
                overlay.classList.remove('ann-visible');
                document.body.style.overflow = '';
            }, 320);
        }

        window.dismissCurrentAnn = function() {
            if (allAnns[currentAnnIdx]) addDismissed(allAnns[currentAnnIdx].id);

            const dismissed = getDismissed();
            allAnns = allAnns.filter(a => !dismissed.includes(a.id));

            if (allAnns.length > 0 && currentAnnIdx < allAnns.length) {
                renderDots();
                showAnn(Math.min(currentAnnIdx, allAnns.length - 1));
            } else {
                hideOverlay();
                showBell(); // always show bell after close
            }
        };

        // Reopen with ALL announcements (including dismissed ones) on bell click
        window.reopenAnn = function() {
            allAnns = allAnnsOriginal.slice(); // restore all
            if (allAnns.length === 0) return;
            renderDots();
            showAnn(0);
        };

        // Close overlay click
        document.getElementById('annPopupOverlay').addEventListener('click', function(e) {
            if (e.target === this) dismissCurrentAnn();
        });

        document.addEventListener('DOMContentLoaded', loadAnnouncements);
    })();
    </script>
</body>
</html>
