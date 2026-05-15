<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel')</title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/animations.css">
    <style>
        :root {
            --bg-body: #f8fafc;
            --bg-sidebar: #ffffff;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --danger: #ef4444;
            --success: #10b981;
            --sidebar-width: 260px;
        }

        :root.dark-mode {
            --bg-body: #0f1015;
            --bg-sidebar: #1b1e2b;
            --bg-card: #1b1e2b;
            --text-main: #ffffff;
            --text-muted: #94a3b8;
            --border-color: #2d3248;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100%;
            top: 0;
            left: 0;
            z-index: 100;
        }

        .brand {
            padding: 24px;
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--text-main);
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .brand svg {
            width: 24px;
            height: 24px;
            color: var(--primary);
        }

        .user-panel {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary);
            border-radius: 50%;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1rem;
        }

        .user-info { display: flex; flex-direction: column; }
        .user-name { font-size: 0.875rem; font-weight: 600; color: var(--text-main); }
        .user-role { font-size: 0.75rem; color: var(--text-muted); }

        nav {
            flex: 1;
            padding: 20px 12px;
            overflow-y: auto;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 16px;
            color: var(--text-muted);
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 4px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .nav-link svg { width: 18px; height: 18px; stroke-width: 2; }
        
        .nav-link:hover, .nav-link.active {
            background: var(--border-color);
            color: var(--primary);
        }
        .nav-link.active {
            background: var(--border-color);
            color: var(--primary);
            font-weight: 600;
        }

        /* Nav Group */
        details { margin-bottom: 4px; }
        details > summary {
            list-style: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        details > summary::-webkit-details-marker { display: none; }
        details > summary .nav-link-content { display: flex; align-items: center; gap: 12px; }
        details > summary .chevron { width: 16px; height: 16px; transition: transform 0.2s; }
        details[open] > summary .chevron { transform: rotate(180deg); }
        .sub-nav { padding-left: 36px; margin-top: 4px; }
        .sub-nav .nav-link { padding: 8px 12px; font-size: 0.85rem; }

        .logout-btn {
            background: none;
            border: none;
            color: var(--danger);
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 24px;
            font-size: 0.875rem;
            border-top: 1px solid var(--border-color);
            transition: background 0.2s;
        }
        .logout-btn:hover { background: #fee2e2; }

        /* --- MAIN CONTENT --- */
        .main-wrapper {
            margin-left: var(--sidebar-width);
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .topbar {
            height: 64px;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
        }

        .topbar-title { font-size: 1.125rem; font-weight: 600; }

        .main-content {
            padding: 32px;
            flex: 1;
            max-width: 1500px;
            margin: 0 auto;
            width: 100%;
        }

        /* --- MODAL GLOBAL FIX --- */
        /* Ensure fixed modals always stack above sidebar + topbar */
        .modal {
            z-index: 9999 !important;
        }
        /* Body scroll lock when modal open */
        body.modal-open {
            overflow: hidden;
        }

        /* --- COMPONENTS --- */
        .card {
            background: var(--bg-card);
            border-radius: 8px;
            border: 1px solid var(--border-color);
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            padding: 24px;
            margin-bottom: 24px;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            cursor: pointer;
            border: 1px solid transparent;
            transition: all 0.2s;
        }
        .btn-primary { background: var(--primary); color: white; }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-secondary { background: var(--bg-body); border-color: var(--border-color); color: var(--text-main); }
        .btn-secondary:hover { background: var(--border-color); }
        .btn-danger { background: var(--danger); color: white; }
        .btn-danger:hover { background: #dc2626; }
        .btn-sm { padding: 4px 10px; font-size: 0.75rem; }

        /* Tables */
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 12px 16px; color: var(--text-muted); font-size: 0.75rem; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid var(--border-color); }
        td { padding: 16px; border-bottom: 1px solid var(--border-color); font-size: 0.875rem; color: var(--text-main); vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; }
        .badge-success { background: #d1fae5; color: #047857; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-danger { background: #fee2e2; color: #b91c1c; }

        .img-thumb { width: 48px; height: 48px; object-fit: cover; border-radius: 6px; background: var(--bg-body); display: flex; align-items: center; justify-content: center; font-size: 0.7rem; color: var(--text-muted); border: 1px solid var(--border-color); }

        /* Forms */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 6px; color: var(--text-main); }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border-color); border-radius: 6px; font-size: 0.875rem; color: var(--text-main); background: var(--bg-body); transition: border-color 0.2s; }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); }
        
        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 24px; font-size: 0.875rem; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #d1fae5; color: #047857; border: 1px solid #a7f3d0; }
        
        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .header-actions h1 { font-size: 1.5rem; font-weight: 600; color: var(--text-main); }

        /* Pagination Fixes — compact right-aligned */
        nav[role="navigation"] {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            flex-wrap: nowrap;
            gap: 4px;
            padding: 16px 0 4px;
        }
        nav[role="navigation"] > div:first-child { display: none; }
        nav[role="navigation"] > div:last-child {
            display: flex !important;
            align-items: center;
            flex-wrap: nowrap;
            gap: 2px;
        }
        
        /* --- MOBILE RESPONSIVE --- */
        .mobile-toggle { display: none; background: none; border: none; color: var(--text-main); padding: 4px; cursor: pointer; }
        .sidebar-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 90; backdrop-filter: blur(2px); }
        
        @media (max-width: 768px) {
            .mobile-toggle { display: block; }
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                box-shadow: 2px 0 10px rgba(0,0,0,0.2);
            }
            .sidebar.open { transform: translateX(0); }
            .sidebar-overlay.open { display: block; }
            .main-wrapper { margin-left: 0; width: 100%; }
            .topbar { padding: 0 16px; }
            .main-content { padding: 16px; }
            .topbar-title { font-size: 1rem; }
            .header-actions { flex-direction: column; align-items: flex-start; gap: 12px; }
            .card { overflow-x: auto; padding: 16px; }
            table { min-width: 700px; }
            .form-grid, .grid { grid-template-columns: 1fr !important; }
            .modal-content { width: 95% !important; margin: 20px auto !important; }
            .topbar .btn { padding: 6px; font-size: 0.75rem; }
            .topbar .btn span { display: none; }
        }

        nav[role="navigation"] span,
        nav[role="navigation"] a {
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            height: 32px;
            padding: 0 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            border: 1px solid var(--border-color);
            white-space: nowrap;
            text-decoration: none;
            color: var(--text-muted);
            transition: all 0.15s;
        }
        nav[role="navigation"] a:hover {
            background-color: var(--bg-surface);
            color: var(--text-main);
            border-color: var(--primary);
        }
        nav[role="navigation"] span[aria-current="page"] span {
            background-color: var(--primary) !important;
            color: white !important;
            border-color: var(--primary) !important;
        }
        nav[role="navigation"] span.cursor-default {
            border: none;
            background: transparent;
            color: var(--text-muted);
            min-width: unset;
        }
        nav[role="navigation"] svg { width: 14px; height: 14px; }
    </style>
</head>
<body>
    <script>
        if (localStorage.getItem('abuser_theme') !== 'light') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>

    <div class="sidebar anim-slide-left">
        <div class="brand">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            <span data-tr="admin_panel">Admin Panel</span>
        </div>
        
        <div class="user-panel">
            <div class="user-avatar">
                {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="user-info">
                <div class="user-name">{{ Auth::user()->name ?? 'Admin' }}</div>
                <div class="user-role">Administrator</div>
            </div>
        </div>
        
        <nav>
            <details {{ request()->routeIs('admin.dashboard') || request()->routeIs('admin.transactions.*') || request()->routeIs('products.create') ? 'open' : '' }}>
                <summary class="nav-link">
                    <div class="nav-link-content">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                        <span data-tr="products_sales">Products & Sales</span>
                    </div>
                    <svg class="chevron" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </summary>
                <div class="sub-nav">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span data-tr="product_list">Product List</span></a>
                    <a href="{{ route('admin.transactions.index') }}" class="nav-link {{ request()->routeIs('admin.transactions.*') ? 'active' : '' }}"><span data-tr="transaction_history">Transaction History</span></a>
                </div>
            </details>

            <a href="{{ route('admin.promos') }}" class="nav-link {{ request()->routeIs('admin.promos') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                <span data-tr="manage_promos">Manage Promos</span>
            </a>

            <a href="{{ route('admin.users') }}" class="nav-link {{ request()->routeIs('admin.users') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span data-tr="active_users">Active Users</span>
            </a>

            <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                </svg>
                <span data-tr="manage_reviews">Manage Reviews</span>
            </a>

            <a href="{{ route('admin.announcements') }}" class="nav-link {{ request()->routeIs('admin.announcements') ? 'active' : '' }}">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z" />
                </svg>
                <span data-tr="manage_announcements">Announcements</span>
            </a>
        </nav>

        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="logout-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <span data-tr="sign_out">Sign Out</span>
            </button>
        </form>
    </div>
    
    <div class="sidebar-overlay" onclick="toggleSidebar()"></div>

    <div class="main-wrapper">
        <header class="topbar anim-fade-down">
            <div style="display: flex; align-items: center; gap: 12px;">
                <button class="mobile-toggle" onclick="toggleSidebar()">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="topbar-title">@yield('title', 'Dashboard')</div>
            </div>
            <div style="display: flex; gap: 12px; align-items: center;">
                <button id="langToggleBtn" onclick="toggleLanguage()" class="btn btn-secondary" style="padding: 6px 12px; display: flex; align-items: center; gap: 6px;">
                    <span id="langIcon">🇺🇸</span> <span id="langText">EN</span>
                </button>
                <button id="themeToggleBtn" onclick="toggleTheme()" class="btn btn-secondary" style="padding: 8px; border-radius: 50%;">
                    <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <!-- Default Sun Icon -->
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                </button>
                <a href="/" target="_blank" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                    </svg>
                    <span data-tr="view_website">View Website</span>
                </a>
            </div>
        </header>

        <main class="main-content anim-fade-up delay-2">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="alert" style="background:#fee2e2; color:#b91c1c; border:1px solid #fecaca;">
                    {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.querySelector('.sidebar').classList.toggle('open');
            document.querySelector('.sidebar-overlay').classList.toggle('open');
        }

        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark-mode');
            const icon = document.getElementById('themeIcon');
            if (isDark) {
                // Moon Icon
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />';
            } else {
                // Sun Icon
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />';
            }
        }

        function toggleTheme() {
            document.documentElement.classList.toggle('dark-mode');
            const isDark = document.documentElement.classList.contains('dark-mode');
            localStorage.setItem('abuser_theme', isDark ? 'dark' : 'light');
            updateThemeIcon();
        }

        // --- TRANSLATION LOGIC ---
        const translations = {
            id: {
                admin_panel: "Panel Admin",
                products_sales: "Produk & Penjualan",
                product_list: "List Produk",
                transaction_history: "Riwayat Transaksi",
                manage_promos: "Kelola Promo",
                active_users: "Pengguna Aktif",
                sign_out: "Keluar",
                view_website: "Lihat Website",
                // Admin page titles and general words
                dashboard: "Dasbor",
                transactions: "Transaksi",
                promos: "Promo",
                users: "Pengguna",
                add_product: "Tambah Produk",
                search: "Cari...",
                save: "Simpan",
                delete: "Hapus",
                edit: "Edit",
                cancel: "Batal",
                name: "Nama",
                price: "Harga",
                stock: "Stok",
                action: "Aksi",
                status: "Status",
                date: "Tanggal",
                paid: "LUNAS",
                pending: "TUNDA",
                failed: "GAGAL",
                no_transactions: "Belum ada data transaksi.",
                delete_all_history: "Hapus Semua Riwayat",
                // Promo Descriptions
                manage_promos_title: "Kelola Kode Promo",
                manage_promos_info: "Kelola, buat, dan pantau kode diskon untuk toko Anda. Anda bisa mengatur diskon tetap atau persentase, serta batas penggunaan.",
                create_discount_codes: "Buat kode diskon untuk pelanggan.",
                promo_code_desc: "Kode unik yang dimasukkan pelanggan saat checkout (Contoh: SUMMER50).",
                discount_type_desc: "Pilih antara potongan harga tetap (Rp) atau persentase (%) dari total harga.",
                discount_value_desc: "Angka diskon yang diberikan (Contoh: 5000 atau 10).",
                max_discount_desc: "Batas maksimal potongan untuk tipe Persentase (%). Isi 0 untuk tanpa batas.",
                usage_limit_desc: "Batas pemakaian per pengguna. Isi 0 untuk tanpa batas.",
                min_qty_desc: "Jumlah produk minimal dalam satu transaksi agar promo ini berlaku.",
                specific_product_desc: "Tentukan apakah promo ini hanya berlaku untuk produk tertentu atau semua produk.",
                manage_announcements: "Pengumuman",
                create_announcement: "Buat Pengumuman",
                ann_title: "Judul",
                ann_style: "Gaya",
                ann_schedule: "Jadwal",
                ann_created: "Dibuat Oleh",
                ann_active: "Aktif",
                save_publish: "Simpan & Publikasi"
            }
        };

        let currentLang = localStorage.getItem('abuser_lang_admin') || 'en';

        function updateLanguageUI() {
            const langIcon = document.getElementById('langIcon');
            const langText = document.getElementById('langText');
            
            if (currentLang === 'id') {
                langIcon.textContent = '🇮🇩';
                langText.textContent = 'ID';
            } else {
                langIcon.textContent = '🇺🇸';
                langText.textContent = 'EN';
            }

            document.querySelectorAll('[data-tr]').forEach(el => {
                const key = el.getAttribute('data-tr');
                if (currentLang === 'id' && translations.id[key]) {
                    el.textContent = translations.id[key];
                } else {
                    if (el.hasAttribute('data-en')) {
                        el.textContent = el.getAttribute('data-en');
                    } else {
                        el.setAttribute('data-en', el.textContent);
                    }
                }
            });

            // Handle title translations
            document.querySelectorAll('[data-tr-title]').forEach(el => {
                const key = el.getAttribute('data-tr-title');
                if (currentLang === 'id' && translations.id[key]) {
                    el.setAttribute('title', translations.id[key]);
                } else {
                    if (el.hasAttribute('data-en-title')) {
                        el.setAttribute('title', el.getAttribute('data-en-title'));
                    } else {
                        el.setAttribute('data-en-title', el.getAttribute('title') || '');
                    }
                }
            });
            
            // Custom event so other scripts can hook into language change
            document.dispatchEvent(new Event('languageChanged'));
        }

        function toggleLanguage() {
            currentLang = currentLang === 'en' ? 'id' : 'en';
            localStorage.setItem('abuser_lang_admin', currentLang);
            updateLanguageUI();
        }

        // Init
        document.addEventListener('DOMContentLoaded', () => {
            updateThemeIcon();
            
            // Save initial English text for all data-tr elements
            document.querySelectorAll('[data-tr]').forEach(el => {
                if(!el.hasAttribute('data-en')) {
                    el.setAttribute('data-en', el.textContent);
                }
            });
            // Save initial English title for all data-tr-title elements
            document.querySelectorAll('[data-tr-title]').forEach(el => {
                if(!el.hasAttribute('data-en-title')) {
                    el.setAttribute('data-en-title', el.getAttribute('title') || '');
                }
            });
            updateLanguageUI();
        });
    </script>
</body>
</html>
