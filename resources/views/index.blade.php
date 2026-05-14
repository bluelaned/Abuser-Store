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
</body>
</html>