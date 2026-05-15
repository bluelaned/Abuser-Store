<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop | ABUSER STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    
    <style>
        /* === FLOATING DISCORD BUTTON CSS === */
        .discord-float {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background-color: #5865F2; /* Warna khas Discord */
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
            animation: none; /* Matiin pulse pas dihover */
        }

        .discord-float svg {
            width: 32px;
            height: 32px;
            fill: white;
            transition: 0.3s;
        }

        /* Tooltip (Tulisan pas dihover) */
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
        
        /* Segitiga panah tooltip */
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
            right: 80px; /* Sedikit animasi geser */
        }
        .discord-float:hover::after {
            right: 74px;
        }

        /* Animasi Nyala (Pulse) */
        @keyframes pulse-discord {
            0% { box-shadow: 0 0 0 0 rgba(88, 101, 242, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(88, 101, 242, 0); }
            100% { box-shadow: 0 0 0 0 rgba(88, 101, 242, 0); }
        }

        /* Resposive untuk HP */
        @media (max-width: 768px) {
            .discord-float {
                bottom: 20px;
                right: 20px;
                width: 50px;
                height: 50px;
            }
            .discord-float svg { width: 26px; height: 26px; }
            .discord-float::before, .discord-float::after { display: none; } /* Sembunyiin tooltip di HP */
        }
    </style>
</head>
<body>
    {{-- === GLOBAL LOADING SCREEN === --}}
    <div id="globalLoader" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg-body); z-index: 99999; display: flex; flex-direction: column; align-items: center; justify-content: center; transition: opacity 0.5s ease, visibility 0.5s ease;">
        <h2 style="color: var(--text-main); margin: 0 0 12px 0; font-weight: 900; letter-spacing: 2px; font-size: 2rem;">ABUSER<span style="color: var(--primary);">STORE</span></h2>
        <div style="width: 180px; height: 4px; background: var(--border-color); border-radius: 4px; overflow: hidden; position: relative;">
            <div style="position: absolute; top: 0; left: 0; height: 100%; width: 50%; background: var(--primary); border-radius: 4px; animation: loadingBar 1.2s infinite ease-in-out;"></div>
        </div>
        <style>
            @keyframes loadingBar {
                0% { left: -50%; width: 50%; }
                50% { left: 25%; width: 50%; }
                100% { left: 100%; width: 50%; }
            }
        </style>
    </div>

    <script>
        if (localStorage.getItem('abuser_theme') !== 'light') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>

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
                <!-- USER DROPDOWN -->
                <div class="user-dropdown-wrapper" id="userDropdown">
                    <div class="user-dropdown-toggle" onclick="toggleUserDropdown(event)">
                        <img src="{{ Auth::user()->avatar }}" alt="Avatar" style="width: 28px; height: 28px; border-radius: 50%; border: 1px solid var(--border-color);">
                        <div style="display: flex; flex-direction: column; line-height: 1.2;">
                            <span style="color: var(--text-main); font-weight: 700; font-size: 0.8rem;">{{ strtoupper(Auth::user()->name) }}</span>
                            @if(Auth::user()->provider_name === 'discord')
                                <span style="color: #5865F2; font-size: 0.65rem; font-weight: 700;">DISCORD LINKED</span>
                            @else
                                <span style="color: var(--success); font-size: 0.65rem; font-weight: 700;">VERIFIED</span>
                            @endif
                        </div>
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                    
                    <div class="user-dropdown-menu">
                        <div class="dropdown-header">
                            <img src="{{ Auth::user()->avatar }}" alt="Avatar">
                            <div class="dropdown-header-info">
                                <span class="dropdown-header-name">{{ Auth::user()->name }}</span>
                                <span class="dropdown-header-role">{{ ucfirst(Auth::user()->role) }}</span>
                            </div>
                        </div>
                        <div class="dropdown-links">
                            <a href="{{ route('profile.show', ['name' => strtolower(Auth::user()->name), 'id' => Auth::id()]) }}" class="dropdown-link">Your profile</a>
                            @if(Auth::user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-link" style="color: #00aaff;">Admin Panel</a>
                            @endif
                            <div class="dropdown-divider"></div>
                            <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                                @csrf
                                <button type="submit" class="dropdown-link" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer;">Log out</button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <a href="{{ route('auth.discord') }}" style="background-color: #5865F2; color: white; padding: 8px 16px; border-radius: 20px; text-decoration: none; font-weight: bold; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; transition: 0.3s; box-shadow: 0 4px 10px rgba(88, 101, 242, 0.3);" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                    <svg style="width: 18px; height: 18px; fill: white;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.14 96.36"><path d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,46,53.89,53,48.84,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.31,60,73.31,53s5-12.74,11.43-12.74S96.3,46,96.19,53,91.08,65.69,84.69,65.69Z"/></svg>
                    LOGIN
                </a>
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


    <div class="hero">
        <h1 id="heroTitle" class="anim-fade-up delay-2">Premium Digital Goods</h1>
        <p id="heroDesc" class="anim-fade-up delay-4">Stay active. Stay feared. Dominate them all.</p>
    </div>



    <div class="container">

        {{-- === REAL PRODUCT GRID === --}}
        <div class="grid" id="productGrid">
            @forelse($products as $product)
                @php
                    $minIdr = $product->variants->min('price');
                    $minUsd = $product->variants->min('price_usd') ?? 0;
                @endphp

                <div class="card reveal">
                    <div class="card-inner">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="card-img" alt="{{ $product->name }}">
                        @else
                            <div class="card-img" style="display:flex; align-items:center; justify-content:center; color:#555; font-weight: bold;">NO IMAGE</div>
                        @endif
                        
                        <div class="card-body">
                            <div class="price-tag">
                                <span class="starts-text">Starts from</span> 
                                <span class="dynamic-price">
                                    $ {{ number_format($minUsd, 2) }}
                                </span>
                            </div>

                            <div class="product-header-row">
                                <h3 class="product-name" title="{{ $product->name }}">{{ $product->name }}</h3>
                                <div class="type-label">{{ $product->type }}</div>
                            </div>
                            
                            <button onclick="goToCheckout('{{ $product->id }}')" class="btn-buy">BUY NOW</button>
                        </div>
                    </div>
                </div>
            @empty
                <div style="grid-column: 1/-1; text-align: center; padding: 80px; color: var(--text-muted); border: 2px dashed var(--border-color); border-radius: 20px; background: var(--bg-surface);">
                    <h3 id="emptyTitle" style="color: var(--text-main); margin: 0; font-weight: 600;">No products yet.</h3>
                </div>
            @endforelse
        </div>
    </div>

    {{-- === LEAVE A REVIEW SECTION MOVED TO REVIEWS PAGE === --}}


    <footer>
        <div style="margin-bottom: 12px;">
            <a href="{{ route('tos') }}" style="color: var(--text-muted); text-decoration: none; margin: 0 10px; font-size: 0.9rem; transition: 0.3s;">Terms of Service</a>
            |
            <a href="{{ route('privacy') }}" style="color: var(--text-muted); text-decoration: none; margin: 0 10px; font-size: 0.9rem; transition: 0.3s;">Privacy Policy</a>
        </div>
        &copy; {{ date('Y') }} ABUSER STORE. All Rights Reserved.
    </footer>

    <a href="https://discord.com/invite/Js7Kpu8wWT" target="_blank" class="discord-float">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 127.14 96.36">
            <path d="M107.7,8.07A105.15,105.15,0,0,0,81.47,0a72.06,72.06,0,0,0-3.36,6.83A97.68,97.68,0,0,0,49,6.83,72.37,72.37,0,0,0,45.64,0,105.89,105.89,0,0,0,19.39,8.09C2.79,32.65-1.71,56.6.54,80.21h0A105.73,105.73,0,0,0,32.71,96.36,77.7,77.7,0,0,0,39.6,85.25a68.42,68.42,0,0,1-10.85-5.18c.91-.66,1.8-1.34,2.66-2a75.57,75.57,0,0,0,64.32,0c.87.71,1.76,1.39,2.66,2a68.68,68.68,0,0,1-10.87,5.19,77,77,0,0,0,6.89,11.1A105.25,105.25,0,0,0,126.6,80.22h0C129.24,52.84,122.09,29.11,107.7,8.07ZM42.45,65.69C36.18,65.69,31,60,31,53s5-12.74,11.43-12.74S54,46,53.89,53,48.84,65.69,42.45,65.69Zm42.24,0C78.41,65.69,73.31,60,73.31,53s5-12.74,11.43-12.74S96.3,46,96.19,53,91.08,65.69,84.69,65.69Z"/>
        </svg>
    </a>
    <script>
        const isLoggedIn = {{ Auth::check() ? 'true' : 'false' }};

        // === USER DROPDOWN ===
        function toggleUserDropdown(e) {
            e.stopPropagation();
            const wrapper = document.getElementById('userDropdown');
            if (wrapper) wrapper.classList.toggle('active');
        }

        document.addEventListener('click', function(e) {
            const userDropdown = document.getElementById('userDropdown');
            if (userDropdown && !userDropdown.contains(e.target)) {
                userDropdown.classList.remove('active');
            }
        });

        // === LOADING SCREEN → REAL CONTENT REVEAL ===
        function revealProducts() {
            const loader = document.getElementById('globalLoader');
            if (loader) {
                loader.style.opacity = '0';
                setTimeout(() => {
                    loader.style.display = 'none';
                    initReveal();
                }, 500);
            } else {
                initReveal();
            }
        }

        // === SCROLL REVEAL ===
        function initReveal() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, i) => {
                    if (entry.isIntersecting) {
                        setTimeout(() => entry.target.classList.add('visible'), i * 60);
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.08 });
            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
        }

        // === STAR RATING LABEL ===
        (function() {
            const labels = { 5: 'Excellent!', 4: 'Very Good!', 3: 'Good', 2: 'Fair', 1: 'Poor' };
            const el = document.getElementById('starLabelText');
            const radios = document.querySelectorAll('.star-picker input[type="radio"]');
            if (!el || !radios.length) return;
            radios.forEach(r => {
                r.addEventListener('change', () => {
                    el.textContent = r.value + ' / 5 — ' + (labels[r.value] || '');
                });
            });
        })();

        // --- CHECKOUT LOGIC ---
        function goToCheckout(productId) {
            window.location.href = `/checkout/${productId}`;
        }

        // --- MODAL UTILS ---
        function closeModal(id) { document.getElementById(id).style.display = 'none'; }



        // --- LANGUAGE (default EN, persisted) ---
        const langConfig = {
            'en': { flag: 'https://flagcdn.com/w20/us.png', label: 'EN',
                    heroTitle: 'Premium Digital Goods',
                    heroDesc:  'Stay active. Stay feared. Dominate them all.',
                    startsFrom: 'Starts from', buyBtn: 'BUY NOW',
                    emptyTitle: 'No products yet.',
                    discord: '"Join Community!"' },
            'id': { flag: 'https://flagcdn.com/w20/id.png', label: 'ID',
                    heroTitle: 'Premium Digital Goods',
                    heroDesc:  'Tetap aktif. Tetap ditakuti. Kuasai segalanya.',
                    startsFrom: 'Mulai dari', buyBtn: 'BELI SEKARANG',
                    emptyTitle: 'Belum ada produk.',
                    discord: '"Gabung Komunitas!"' }
        };

        function toggleLang() { document.getElementById('langOptions').classList.toggle('show'); }
        window.addEventListener('click', (e) => {
            if (!e.target.closest('.lang-wrapper')) document.getElementById('langOptions').classList.remove('show');
        });

        function selectLang(lang, text, flagUrl) {
            document.getElementById('currentFlag').src = flagUrl;
            document.getElementById('currentLangText').innerText = text;
            localStorage.setItem('abuser_lang', lang);
            applyLanguage(lang);
            document.getElementById('langOptions').classList.remove('show');
        }

        function applyLanguage(lang) {
            const t = langConfig[lang] || langConfig['en'];
            const set = (id, val) => { const el = document.getElementById(id); if(el) el.innerText = val; };
            set('heroTitle', t.heroTitle);
            set('heroDesc',  t.heroDesc);
            set('emptyTitle', t.emptyTitle);
            document.querySelectorAll('.starts-text').forEach(el => el.innerText = t.startsFrom);
            document.querySelectorAll('.btn-buy').forEach(el => el.innerText = t.buyBtn);
            const df = document.querySelector('.discord-float');
            if (df) df.style.setProperty('--tooltip-text', t.discord);
        }

        document.head.insertAdjacentHTML('beforeend', `<style>.discord-float::before { content: var(--tooltip-text, "Join Community!"); }</style>`);

        // === DARK MODE LOGIC ===
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

        // === ON DOM READY ===
        document.addEventListener('DOMContentLoaded', () => {
            // Apply saved language (default EN)
            const savedLang = localStorage.getItem('abuser_lang') || 'en';
            const cfg = langConfig[savedLang] || langConfig['en'];
            document.getElementById('currentFlag').src = cfg.flag;
            document.getElementById('currentLangText').innerText = cfg.label;
            applyLanguage(savedLang);

            updateThemeIcon();

            // Show skeleton briefly, then reveal real products
            setTimeout(revealProducts, 600);
        });
    </script>

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
                    <button class="ann-btn-ghost" id="annPrevBtn" onclick="showAnn(currentAnnIdx - 1)">← Prev</button>
                    <button class="ann-btn-primary" id="annNextBtn" onclick="showAnn(currentAnnIdx + 1)">Next →</button>
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
            if (prevBtn) { prevBtn.disabled = idx === 0; prevBtn.style.opacity = idx === 0 ? '0.4' : '1'; }
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