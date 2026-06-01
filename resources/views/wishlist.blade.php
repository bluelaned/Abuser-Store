<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Wishlist | ABUSER STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/animations.css') }}">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
</head>
<body>
    <script>
        if (localStorage.getItem('abuser_theme') !== 'light') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>

    <nav class="navbar">
        <div style="display:flex;align-items:center;gap:16px;">
            <a href="/" class="brand">ABUSER <span>STORE</span></a>
        </div>
        <div style="display:flex;gap:20px;align-items:center;">
            @auth
            <div class="user-dropdown-wrapper" id="userDropdown">
                <div class="user-dropdown-toggle" onclick="toggleUserDropdown(event)">
                    <img src="{{ Auth::user()->avatar }}" alt="Avatar" style="width:28px;height:28px;border-radius:50%;border:1px solid var(--border-color);">
                    <div style="display:flex;flex-direction:column;line-height:1.2;">
                        <span style="color:var(--text-main);font-weight:700;font-size:0.8rem;">{{ strtoupper(Auth::user()->name) }}</span>
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
                        <a href="/" class="dropdown-link">Shop</a>
                        <a href="{{ route('profile.show', ['name' => strtolower(Auth::user()->name), 'id' => Auth::id()]) }}" class="dropdown-link">Profile</a>
                        <a href="{{ route('profile.show', ['name' => strtolower(Auth::user()->name), 'id' => Auth::id()]) }}#transactions" class="dropdown-link">My Orders</a>
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('logout') }}" method="POST" style="margin:0;">
                            @csrf
                            <button type="submit" class="dropdown-link" style="width:100%;border:none;background:none;text-align:left;cursor:pointer;">Log out</button>
                        </form>
                    </div>
                </div>
            </div>
            @endauth
            <button id="themeToggleBtn" onclick="toggleTheme()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;padding:4px;display:flex;">
                <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </button>
        </div>
    </nav>

    <div class="container" style="padding-top:40px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:28px;">
            <div>
                <h1 style="font-size:1.8rem;font-weight:800;color:var(--text-main);margin:0;">❤️ My Wishlist</h1>
                <p style="color:var(--text-muted);margin-top:6px;font-size:0.9rem;">{{ $wishlistItems->count() }} item(s) saved</p>
            </div>
            <a href="/" style="color:var(--text-muted);font-size:0.875rem;text-decoration:none;display:flex;align-items:center;gap:6px;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                Back to Shop
            </a>
        </div>

        @if($wishlistItems->isEmpty())
            <div style="text-align:center;padding:80px 20px;border:2px dashed var(--border-color);border-radius:20px;background:var(--bg-surface,var(--bg-body));">
                <div style="font-size:3rem;margin-bottom:16px;">💔</div>
                <h3 style="color:var(--text-main);font-size:1.2rem;font-weight:700;margin:0 0 8px;">Your wishlist is empty</h3>
                <p style="color:var(--text-muted);font-size:0.9rem;margin:0 0 24px;">Browse the shop and save products you like!</p>
                <a href="/" style="background:var(--primary);color:white;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:700;font-size:0.9rem;">Browse Shop</a>
            </div>
        @else
            <div class="grid" id="wishlistGrid">
                @foreach($wishlistItems as $item)
                @php $product = $item->product; $minUsd = $product->variants->min('price_usd') ?? 0; @endphp
                <div class="card reveal" id="wl-card-{{ $product->id }}" style="position:relative;">
                    <div class="card-inner">
                        @if($product->image)
                            <img src="{{ asset('storage/' . $product->image) }}" class="card-img" alt="{{ $product->name }}">
                        @else
                            <div class="card-img" style="display:flex;align-items:center;justify-content:center;color:#555;font-weight:bold;">NO IMAGE</div>
                        @endif
                        {{-- Remove from wishlist --}}
                        <button onclick="removeFromWishlist({{ $product->id }})" style="position:absolute;top:10px;right:10px;background:rgba(239,68,68,0.9);border:none;color:white;width:32px;height:32px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:0.9rem;transition:0.2s;" title="Remove from wishlist" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">✕</button>
                        <div class="card-body">
                            <div class="price-tag">
                                <span class="starts-text">Starts from</span>
                                <span class="dynamic-price">$ {{ number_format($minUsd, 2) }}</span>
                            </div>
                            <div class="product-header-row">
                                <h3 class="product-name" title="{{ $product->name }}">{{ $product->name }}</h3>
                                <div class="type-label">{{ $product->type }}</div>
                            </div>
                            <button onclick="window.location.href='/checkout/{{ $product->id }}'" class="btn-buy">BUY NOW</button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <footer style="text-align:center;padding:40px 20px;color:var(--text-muted);font-size:0.875rem;margin-top:60px;">
        &copy; {{ date('Y') }} ABUSER STORE. All Rights Reserved.
    </footer>

    <script>
        function toggleUserDropdown(e) {
            e.stopPropagation();
            document.getElementById('userDropdown')?.classList.toggle('active');
        }
        document.addEventListener('click', function(e) {
            const d = document.getElementById('userDropdown');
            if (d && !d.contains(e.target)) d.classList.remove('active');
        });

        function toggleTheme() {
            document.documentElement.classList.toggle('dark-mode');
            localStorage.setItem('abuser_theme', document.documentElement.classList.contains('dark-mode') ? 'dark' : 'light');
            updateThemeIcon();
        }
        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark-mode');
            const icon = document.getElementById('themeIcon');
            if (isDark) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />';
            }
        }
        document.addEventListener('DOMContentLoaded', () => {
            updateThemeIcon();
            const observer = new IntersectionObserver((entries) => {
                entries.forEach((entry, i) => {
                    if (entry.isIntersecting) { setTimeout(() => entry.target.classList.add('visible'), i * 60); observer.unobserve(entry.target); }
                });
            }, { threshold: 0.08 });
            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));
        });

        async function removeFromWishlist(productId) {
            const card = document.getElementById('wl-card-' + productId);
            try {
                const res = await fetch(`/wishlist/${productId}/toggle`, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'application/json' }
                });
                const data = await res.json();
                if (!data.in_wishlist && card) {
                    card.style.transition = 'opacity 0.3s, transform 0.3s';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => card.remove(), 300);
                }
            } catch(e) { console.error(e); }
        }
    </script>
</body>
</html>
