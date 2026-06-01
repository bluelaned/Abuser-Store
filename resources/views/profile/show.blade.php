<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $user->name }}'s Profile</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.css" />
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <style>
        :root {
            --bg-color: #0f0f11;
            --bg-body: #0f0f11;
            --bg-card: #161618;
            --bg-surface: #1e1e21;
            --border-color: #2a2a2d;
            --text-main: #fcfcfc;
            --text-muted: #8b8b93;
            --primary: #4f46e5;
            --primary-dim: #6366f1;
            --accent: #10b981;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 1100px;
            margin: 100px auto 60px auto;
            padding: 0 20px;
        }

        .profile-card {
            background-color: var(--bg-card);
            border-radius: 16px;
            border: 1px solid var(--border-color);
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.4);
            position: relative;
        }

        /* Banner */
        .profile-banner {
            height: 180px;
            background-color: #1a1a1a;
            background-size: cover;
            background-position: center 35%;
            position: relative;
        }

        /* Override index.css navbar elements for dark profile page */
        .navbar { background: #161618 !important; border-bottom-color: #2a2a2d !important; }
        .user-dropdown-toggle { background: #0f0f11 !important; border-color: #2a2a2d !important; }
        .user-dropdown-toggle span, .user-dropdown-toggle svg { color: #fcfcfc !important; }
        .user-dropdown-menu { background: #161618 !important; border-color: #2a2a2d !important; }
        .dropdown-header { background: #0f0f11 !important; border-color: #2a2a2d !important; }
        .dropdown-header-name { color: #fcfcfc !important; }
        .dropdown-header-role { color: #8b8b93 !important; }
        .dropdown-link { color: #fcfcfc !important; }
        .dropdown-link:hover { background: rgba(79,70,229,0.15) !important; color: #6366f1 !important; }
        .dropdown-divider { background: #2a2a2d !important; }
        .lang-selected { background: #0f0f11 !important; border-color: #2a2a2d !important; color: #fcfcfc !important; }
        .lang-dropdown { background: #161618 !important; border-color: #2a2a2d !important; }
        .lang-option { color: #fcfcfc !important; }
        .lang-option:hover { background: rgba(79,70,229,0.15) !important; color: #6366f1 !important; }
        .reviews-nav-btn { color: #8b8b93 !important; border-color: #2a2a2d !important; }
        .reviews-nav-btn:hover { color: #6366f1 !important; border-color: #6366f1 !important; }
        .brand { color: #fcfcfc !important; }
        #themeToggleBtn { color: #8b8b93 !important; }

        .profile-banner-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, var(--bg-card) 0%, rgba(22,22,24,0) 60%);
        }

        /* Top Right Actions */
        .banner-actions {
            position: absolute;
            top: 24px;
            right: 24px;
            display: flex;
            gap: 12px;
            z-index: 10;
        }

        .btn {
            background: rgba(0, 0, 0, 0.4);
            color: var(--text-main);
            border: 1px solid rgba(255,255,255,0.1);
            padding: 8px 20px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255,255,255,0.3);
            transform: translateY(-1px);
        }

        /* Profile Header */
        .profile-header {
            position: relative;
            padding: 0 40px 24px 40px;
            display: flex;
            align-items: flex-end;
            margin-top: -60px;
        }

        .avatar-container {
            position: relative;
            z-index: 5;
            margin-right: 32px;
            flex-shrink: 0;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 16px;
            border: 5px solid var(--bg-card);
            object-fit: cover;
            box-shadow: 0 8px 24px rgba(0,0,0,0.6);
            background-color: var(--bg-surface);
        }

        .profile-info {
            flex: 1;
            padding-bottom: 12px;
            z-index: 5;
        }

        .profile-name {
            font-size: 2.25rem;
            font-weight: 800;
            margin: 0 0 4px 0;
            line-height: 1.1;
            letter-spacing: -0.5px;
        }

        .profile-role {
            font-size: 1rem;
            font-weight: 600;
            margin: 0 0 12px 0;
            color: var(--primary-dim);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .profile-meta {
            display: flex;
            gap: 20px;
            font-size: 0.9rem;
            color: var(--text-muted);
            margin: 0;
        }

        .profile-meta span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Stats Bar */
        .profile-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1px;
            background-color: var(--border-color);
            border-top: 1px solid var(--border-color);
            border-bottom: 1px solid var(--border-color);
        }

        .stat-item {
            background-color: var(--bg-card);
            padding: 24px 32px;
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            transition: background-color 0.3s ease;
        }

        .stat-item:hover {
            background-color: var(--bg-surface);
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-main);
        }

        .stat-value.accent {
            color: var(--accent);
        }

        /* Tabs */
        .profile-tabs {
            display: flex;
            padding: 0 40px;
            gap: 32px;
            border-bottom: 1px solid var(--border-color);
            background-color: var(--bg-surface);
        }

        .tab-link {
            padding: 20px 0;
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 600;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        .tab-link:hover {
            color: var(--text-main);
        }

        .tab-link.active {
            color: var(--primary-dim);
            border-bottom-color: var(--primary-dim);
        }

        /* Content Area */
        .profile-content {
            padding: 32px 40px;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            align-items: stretch;
            justify-content: flex-start;
            width: 100%;
            box-sizing: border-box;
        }
        .profile-content.centered {
            align-items: center;
            justify-content: center;
        }

        /* Banner Upload Modal */
        #bannerModal {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.8);
            z-index: 9999;
            align-items: center; justify-content: center;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            animation: modalFadeIn 0.3s ease;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: scale(0.95); }
            to { opacity: 1; transform: scale(1); }
        }

        .modal-box {
            background: var(--bg-card);
            padding: 32px;
            border-radius: 16px;
            border: 1px solid var(--border-color);
            width: 450px;
            max-width: 90%;
            box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
        }
        .form-group { margin-bottom: 24px; }
        .form-control {
            width: 100%; padding: 12px;
            background: rgba(0,0,0,0.2);
            color: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            outline: none;
            transition: border-color 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary-dim);
        }

        /* Serial Key Blur */
        .serial-key-blur {
            filter: blur(5px);
        }
        .serial-key-blur:hover {
            filter: blur(3px);
        }
        .serial-key-blur.revealed {
            filter: blur(0px);
            user-select: text !important;
        }

        /* Custom Pagination */
        nav .d-sm-none { display: none !important; }
        nav p.text-muted { display: none !important; }
        nav .d-none.d-sm-flex { display: flex !important; justify-content: flex-end; width: 100%; }

        .pagination {
            display: flex;
            gap: 6px;
            list-style: none;
            padding: 0;
            margin: 0;
            align-items: center;
        }
        .pagination .page-item .page-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 6px 14px;
            min-width: 36px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface);
            color: var(--text-main);
            text-decoration: none;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        .pagination .page-item.active .page-link {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        .pagination .page-item.disabled .page-link {
            opacity: 0.5;
            cursor: not-allowed;
        }
        .pagination .page-item:not(.disabled):not(.active) .page-link:hover {
            border-color: var(--primary-dim);
            color: var(--primary);
            background: rgba(79, 70, 229, 0.1);
        }

        @keyframes tierBadgeBob {
            0%, 100% { transform: translateY(0) scale(1); }
            50%       { transform: translateY(-4px) scale(1.06); }
        }

        /* ═══════════════════════════════════════════════
           TIER FRAME SYSTEM — Premium Animated Frames
        ═══════════════════════════════════════════════ */

        .tier-frame-container {
            position: absolute;
            inset: -8px;
            border-radius: 50%;
            z-index: 2;
            pointer-events: none;
        }


    </style>
</head>
<body>
    <script>
        if (localStorage.getItem('abuser_theme') !== 'light') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>
    <nav class="navbar anim-fade-down" style="position: fixed; top: 0; left: 0; right: 0; z-index: 100;">
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
                    LOGIN
                </a>
            @endauth

            <!-- Theme Toggle -->
            <button id="themeToggleBtn" onclick="toggleTheme()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding: 4px; display:flex;">
                <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </button>

            <!-- Language Dropdown -->
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

<div class="container">
    @if(session('success'))
        <div style="background: rgba(16, 185, 129, 0.1); color: #10b981; padding: 12px; border-radius: 6px; margin-bottom: 16px; border: 1px solid rgba(16, 185, 129, 0.2);">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 6px; margin-bottom: 16px; border: 1px solid rgba(239, 68, 68, 0.2);">
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div style="background: rgba(239, 68, 68, 0.1); color: #ef4444; padding: 12px; border-radius: 6px; margin-bottom: 16px; border: 1px solid rgba(239, 68, 68, 0.2);">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="profile-card">
        <!-- Banner Section -->
        <div class="profile-banner" style="background-image: url('{{ $user->banner ? asset($user->banner) : asset('images/default-banner.png') }}'); background-color: #1e1e21;">
            <div class="profile-banner-overlay"></div>

            <div class="banner-actions">
                <a href="{{ url('/') }}" class="btn">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Back to Store
                </a>

                @if(auth()->check() && (auth()->id() === $user->id || auth()->user()->role === 'admin'))
                    @if($user->banner)
                        <form action="{{ route('profile.update_banner', ['name' => strtolower($user->name), 'id' => $user->id]) }}" method="POST" style="margin: 0;">
                            @csrf
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn" style="color: #fca5a5; border-color: rgba(239, 68, 68, 0.3);" onclick="return confirm('Hapus banner profil?');">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete Banner
                            </button>
                        </form>
                    @endif
                    <button class="btn" onclick="document.getElementById('bannerModal').style.display='flex'">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Edit Banner
                    </button>
                @endif
            </div>
        </div>

        <!-- Header Info -->
        <div class="profile-header">
            <div class="avatar-container" style="position: relative; width: 120px; height: 120px; margin-right: 32px; cursor: {{ (auth()->check() && (auth()->id() === $user->id || auth()->user()->role === 'admin')) ? 'pointer' : 'default' }};" @if(auth()->check() && (auth()->id() === $user->id || auth()->user()->role === 'admin')) onclick="openAvatarModal()" @endif>
                @if($equippedFrame)
                <!-- Selected Circular Frame -->
                <div class="tier-frame-container">
                    <img src="{{ asset('images/tiers/frame_' . $equippedFrame['id'] . '.png') }}" alt="{{ $equippedFrame['name'] }} Frame" style="width: 100%; height: 100%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(1.35); pointer-events: none; z-index: 10; object-fit: contain;">
                </div>
                @endif
                <!-- Circular Avatar -->
                <img src="{{ $user->avatar ?? 'https://ui-avatars.com/api/?name='.$user->name.'&background=random' }}" alt="Avatar" class="profile-avatar" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover; position: relative; z-index: 1; border: 4px solid var(--bg-card); background: var(--bg-surface);">

                @if(auth()->check() && (auth()->id() === $user->id || auth()->user()->role === 'admin'))
                <!-- Edit Overlay -->
                <div style="position: absolute; inset: 0; background: rgba(0,0,0,0.5); border-radius: 50%; z-index: 3; display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0'">
                    <svg width="24" height="24" fill="none" stroke="#fff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                @endif
            </div>

            <div class="profile-info">
                <div style="display: flex; align-items: center; gap: 12px; position: relative; width: fit-content;">
                    <h1 class="profile-name" style="margin: 0; {{ $tier['id'] === 'diamond' ? 'color: #60a5fa; text-shadow: 0 0 15px rgba(96,165,250,0.5);' : '' }}">
                        {{ $user->name }}
                    </h1>
                    @if($tier['id'] === 'diamond')
                    <div style="position: absolute; inset: 0; background: url('{{ asset('images/diamond_name.gif') }}') center / cover; pointer-events: none; z-index: 10; mix-blend-mode: screen;"></div>
                    @endif
                </div>
                <p class="profile-role">{{ ucfirst($user->role) }}</p>
                <div class="profile-meta">
                    <span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        Joined {{ $user->created_at ? $user->created_at->format('M d, Y') : 'Unknown' }}
                    </span>
                    <span>
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Last seen {{ $user->last_seen ? \Carbon\Carbon::parse($user->last_seen)->diffForHumans() : 'Recently' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Statistics -->
        <div class="profile-stats">
            <div class="stat-item">
                <div class="stat-label">Purchases</div>
                <div class="stat-value">{{ $purchases }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Total Spent</div>
                <div class="stat-value" style="color: var(--accent);">{{ number_format($totalSpent, 0, ',', '.') }}</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Messages</div>
                <div class="stat-value">0</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Reaction Score</div>
                <div class="stat-value">0</div>
            </div>
            <div class="stat-item">
                <div class="stat-label">Membership</div>
                <div class="stat-value" style="color: {{ $tier['color'] }}; text-shadow: 0 0 15px {{ $tier['color'] }}60; display: flex; align-items: center; gap: 8px;">
                    {{ $tier['name'] }}
                    <img src="{{ $tier['icon'] }}" alt="Tier" style="height: 24px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.3));">
                </div>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="profile-tabs">
            @if(auth()->check() && (auth()->id() === $user->id || auth()->user()->role === 'admin'))
                <div class="tab-link active" onclick="showTab('transactions', this)">Transaction history</div>
                <div class="tab-link" onclick="showTab('activity', this)">Recent activity</div>
            @endif
            <div class="tab-link {{ !(auth()->check() && (auth()->id() === $user->id || auth()->user()->role === 'admin')) ? 'active' : '' }}" onclick="showTab('about', this)">About</div>
        </div>

        <div class="profile-content" id="tab-transactions" style="display: {{ (auth()->check() && (auth()->id() === $user->id || auth()->user()->role === 'admin')) ? 'flex' : 'none' }}">
            @if(auth()->check() && (auth()->id() === $user->id || auth()->user()->role === 'admin'))
                <div style="width: 100%;">

                    {{-- Status Filter --}}
                    <div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; align-items: center;">
                        @php
                            $profileUrl = route('profile.show', ['name' => strtolower($user->name), 'id' => $user->id]);
                        @endphp
                        @foreach(['' => 'All', 'PAID' => '✅ Paid', 'UNPAID' => '⏳ Pending', 'FAILED' => '❌ Failed'] as $val => $label)
                            <a href="{{ $profileUrl }}{{ $val ? '?tx_status='.$val : '' }}#transactions"
                               style="padding: 4px 14px; border-radius: 20px; font-size: 0.78rem; font-weight: 600; text-decoration: none; border: 1px solid {{ ($txStatusFilter ?? '') === $val ? '#6366f1' : 'var(--border-color)' }}; color: {{ ($txStatusFilter ?? '') === $val ? '#6366f1' : 'var(--text-muted)' }}; background: {{ ($txStatusFilter ?? '') === $val ? 'rgba(99,102,241,0.12)' : 'transparent' }}; transition: 0.2s;">
                                {{ $label }}
                            </a>
                        @endforeach
                        @if(!empty($txStatusFilter))
                            <span style="font-size: 0.75rem; color: var(--text-muted);">{{ $transactions->total() }} result(s)</span>
                        @endif
                    </div>

                    @if($transactions->isEmpty())
                        <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 150px;">
                            <svg width="48" height="48" fill="none" stroke="var(--border-color)" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom: 12px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                            <p style="color: var(--text-muted); margin: 0;">No transactions{{ $txStatusFilter ? ' with status '.strtolower($txStatusFilter) : '' }} yet.</p>
                        </div>
                    @else
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse; text-align: left;">
                                <thead>
                                    <tr style="border-bottom: 1px solid var(--border-color); color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px;">
                                        <th style="padding: 12px 16px;">Date</th>
                                        <th style="padding: 12px 16px;">Invoice</th>
                                        <th style="padding: 12px 16px;">Product</th>
                                        <th style="padding: 12px 16px;">Serial / Key</th>
                                        <th style="padding: 12px 16px;">Amount</th>
                                        <th style="padding: 12px 16px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transactions as $trx)
                                    <tr style="border-bottom: 1px solid var(--border-color); transition: background 0.15s;" onmouseover="this.style.background='rgba(79,70,229,0.05)'" onmouseout="this.style.background='transparent'">
                                        <td style="padding: 14px 16px; color: var(--text-muted); font-size: 0.875rem; white-space: nowrap;">{{ $trx->created_at->format('M d, Y') }}</td>
                                        <td style="padding: 14px 16px; color: var(--text-main); font-family: monospace; font-size: 0.82rem;">{{ $trx->reference }}</td>
                                        <td style="padding: 14px 16px; color: var(--text-main); font-weight: 600;">
                                            {{ $trx->product_name ?? 'Unknown Product' }}
                                            @if(($trx->quantity ?? 1) > 1)
                                                <span style="color: var(--text-muted); font-weight: 400; font-size: 0.8rem;"> ×{{ $trx->quantity }}</span>
                                            @endif
                                        </td>
                                        <td style="padding: 14px 16px;">
                                            @if($trx->status === 'PAID' && $trx->vouchers_issued)
                                                <span
                                                    class="serial-key-blur"
                                                    onclick="this.classList.toggle('revealed'); copyToClipboard(this.textContent.trim())"
                                                    title="Click to reveal & copy"
                                                    style="font-family: monospace; font-size: 0.82rem; background: rgba(16,185,129,0.08); color: #10b981; padding: 4px 10px; border-radius: 6px; cursor: pointer; display: inline-block; user-select: none; transition: filter 0.3s;"
                                                >{{ $trx->vouchers_issued }}</span>
                                            @elseif($trx->status === 'UNPAID' && $trx->checkout_url)
                                                <a href="{{ $trx->checkout_url }}" target="_blank"
                                                   style="display: inline-flex; align-items: center; gap: 5px; background: rgba(234,179,8,0.12); color: #eab308; border: 1px solid rgba(234,179,8,0.3); padding: 4px 12px; border-radius: 6px; font-size: 0.78rem; font-weight: 700; text-decoration: none; transition: 0.2s;"
                                                   onmouseover="this.style.background='rgba(234,179,8,0.22)'" onmouseout="this.style.background='rgba(234,179,8,0.12)'">
                                                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                                    Continue Payment
                                                </a>
                                            @else
                                                <span style="color: var(--text-muted); font-size: 0.8rem;">—</span>
                                            @endif
                                        </td>
                                        <td style="padding: 14px 16px; color: var(--accent); font-weight: 700; white-space: nowrap;">
                                            @if(in_array(strtoupper($trx->payment_method ?? ''), ['STRIPE', 'PAYPAL', 'CRYPTO']))
                                                $ {{ number_format($trx->price / 100, 2) }}
                                            @else
                                                Rp {{ number_format($trx->price, 0, ',', '.') }}
                                            @endif
                                        </td>
                                        <td style="padding: 14px 16px;">
                                            @if($trx->status === 'PAID')
                                                <span style="background: rgba(16,185,129,0.12); color: #10b981; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;">PAID</span>
                                            @elseif($trx->status === 'UNPAID')
                                                <span style="background: rgba(234,179,8,0.12); color: #eab308; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;">PENDING</span>
                                            @else
                                                <span style="background: rgba(239,68,68,0.12); color: #ef4444; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px;">{{ $trx->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            @if($transactions->hasPages())
                                <div style="margin-top: 20px; display: flex; justify-content: flex-end;">
                                    {{ $transactions->links('pagination::bootstrap-5') }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            @endif
        </div>

        <div class="profile-content" id="tab-activity" style="display: none; align-items: center; justify-content: center;">
            <p style="color: var(--text-muted);">No recent activity.</p>
        </div>

        {{-- ABOUT TAB --}}
        <div class="profile-content" id="tab-about" style="display: none; flex-direction: column; align-items: flex-start; width: 100%;">
            <div style="width: 100%;">

                @if(auth()->check() && (auth()->id() === $user->id || auth()->user()->role === 'admin'))
                    {{-- === READ VIEW === --}}
                    <div id="about-read-view">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <p style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin: 0;">About Me</p>
                            <button onclick="toggleAboutEdit(true)" style="background: rgba(99,102,241,0.12); color: #6366f1; border: 1px solid rgba(99,102,241,0.3); padding: 6px 16px; border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px; transition: all 0.2s;" onmouseover="this.style.background='rgba(99,102,241,0.22)'" onmouseout="this.style.background='rgba(99,102,241,0.12)'">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </button>
                        </div>
                        @if($user->bio)
                            <div class="bio-rendered" style="color: var(--text-main); line-height: 1.85; font-size: 0.95rem; word-break: break-word; white-space: pre-wrap;">{!! renderBioMarkdown($user->bio) !!}</div>
                        @else
                            <p style="color: var(--text-muted); font-style: italic; margin: 0;">You haven't written anything about yourself yet. Click <strong>Edit</strong> to add a bio.</p>
                        @endif
                    </div>

                    {{-- === EDIT FORM === --}}
                    <div id="about-edit-view" style="display: none;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <p style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin: 0;">Editing About Me</p>
                            <button onclick="toggleAboutEdit(false)" style="background: transparent; color: var(--text-muted); border: none; font-size: 0.8rem; cursor: pointer; display: flex; align-items: center; gap: 4px;" onmouseover="this.style.color='var(--text-main)'" onmouseout="this.style.color='var(--text-muted)'">
                                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                Cancel
                            </button>
                        </div>

                        {{-- Formatting Toolbar --}}
                        <div style="display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 8px; background: rgba(0,0,0,0.2); border: 1px solid var(--border-color); border-bottom: none; border-radius: 8px 8px 0 0; padding: 8px 10px;">
                            <button type="button" onclick="bioFormat('bold')" title="Bold (**text**)" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-main); padding: 4px 10px; border-radius: 5px; cursor: pointer; font-weight: 700; font-size: 0.85rem;">B</button>
                            <button type="button" onclick="bioFormat('italic')" title="Italic (*text*)" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-main); padding: 4px 10px; border-radius: 5px; cursor: pointer; font-style: italic; font-size: 0.85rem;">I</button>
                            <button type="button" onclick="bioFormat('strike')" title="Strikethrough (~~text~~)" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-main); padding: 4px 10px; border-radius: 5px; cursor: pointer; font-size: 0.85rem; text-decoration: line-through;">S</button>
                            <div style="width: 1px; background: var(--border-color); margin: 0 4px;"></div>
                            <button type="button" onclick="bioEmojiPicker()" title="Emoji" style="background: transparent; border: 1px solid var(--border-color); color: var(--text-main); padding: 4px 10px; border-radius: 5px; cursor: pointer; font-size: 1rem;">😊</button>
                            <span style="margin-left: auto; font-size: 0.72rem; color: var(--text-muted); align-self: center;"><span id="bioCharCount">0</span> / 1000</span>
                        </div>

                        {{-- Emoji Picker Panel --}}
                        <div id="emojiPanel" style="display:none; background: rgba(15,15,20,0.98); border: 1px solid var(--border-color); border-radius: 10px; padding: 12px; margin-bottom: 8px; max-height: 180px; overflow-y: auto;">
                            <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                                @foreach(['😊','😂','🤣','❤️','😍','🤔','😎','🔥','✅','⭐','🎮','🎯','💀','👍','👎','😭','😡','🤝','💪','🙏','🎉','🚀','💡','⚡','🌟','😴','🤯','🥳','😏','💯','❌','🛡️','🎁','📌','🔑','⚠️','🎵','🎬','🖥️','📱','💻','🏆','🦾','👾','🕹️','💰','📊'] as $em)
                                    <button type="button" onclick="insertEmoji('{{ $em }}')" style="background: transparent; border: none; font-size: 1.3rem; cursor: pointer; padding: 4px; border-radius: 5px; transition: background 0.1s;" onmouseover="this.style.background='rgba(255,255,255,0.08)'" onmouseout="this.style.background='transparent'">{{ $em }}</button>
                                @endforeach
                            </div>
                        </div>

                        <form action="{{ route('profile.update_bio', ['name' => strtolower($user->name), 'id' => $user->id]) }}" method="POST">
                            @csrf
                            <textarea id="bioTextarea" name="bio" placeholder="Write something about yourself... Supports **bold**, *italic*, ~~strikethrough~~" maxlength="1000"
                                style="width: 100%; background: rgba(0,0,0,0.3); color: var(--text-main); border: 1px solid var(--border-color); border-radius: 0 0 10px 10px; padding: 14px; font-family: 'Inter', sans-serif; font-size: 0.9rem; resize: vertical; min-height: 140px; outline: none; transition: border-color 0.2s; box-sizing: border-box; line-height: 1.7;"
                                onfocus="this.style.borderColor='#6366f1'" onblur="this.style.borderColor='var(--border-color)'"
                                oninput="updateBioCount(this)"
                            >{{ old('bio', $user->bio) }}</textarea>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 8px;">
                                <small style="color: var(--text-muted); font-size: 0.72rem;">Supports <code style="background:rgba(255,255,255,0.06); padding: 1px 5px; border-radius:3px;">**bold**</code>, <code style="background:rgba(255,255,255,0.06); padding: 1px 5px; border-radius:3px;">*italic*</code>, <code style="background:rgba(255,255,255,0.06); padding: 1px 5px; border-radius:3px;">~~strike~~</code>. No links allowed.</small>
                                <div style="display: flex; gap: 10px;">
                                    <button type="button" onclick="toggleAboutEdit(false)" style="background: transparent; color: var(--text-muted); border: 1px solid var(--border-color); padding: 8px 20px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer;">Cancel</button>
                                    <button type="submit" style="background: linear-gradient(135deg, #4f46e5, #6366f1); color: white; border: none; padding: 8px 24px; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; transition: opacity 0.2s;" onmouseover="this.style.opacity='0.85'" onmouseout="this.style.opacity='1'">Save</button>
                                </div>
                            </div>
                        </form>
                    </div>

                @else
                    {{-- Read-only for others --}}
                    <p style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; margin: 0 0 16px;">About Me</p>
                    @if($user->bio)
                        <div class="bio-rendered" style="color: var(--text-main); line-height: 1.85; font-size: 0.95rem; word-break: break-word; white-space: pre-wrap;">{!! renderBioMarkdown($user->bio) !!}</div>
                    @else
                        <p style="color: var(--text-muted); font-style: italic; margin: 0;">This user hasn't written anything about themselves yet.</p>
                    @endif
                @endif

            </div>

            {{-- ─── MEMBERSHIP TIERS SHOWCASE ─── --}}
            <div style="width: 100%; margin-top: 32px; border-top: 1px solid var(--border-color); padding-top: 28px;">
                <p style="color: var(--text-muted); font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; margin: 0 0 6px; text-align:center;">◆ MEMBERSHIP TIERS ◆</p>
                <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0 0 24px; text-align:center; opacity:0.7;">The more you spend, the higher your membership tier.</p>

                @php
                    $allTiers  = [
                        ['id'=>'bronze',   'name'=>'Bronze',   'color'=>'#cd7f32', 'threshold'=>0,    'label'=>'$0+'],
                        ['id'=>'silver',   'name'=>'Silver',   'color'=>'#cbd5e1', 'threshold'=>100,  'label'=>'$100+'],
                        ['id'=>'gold',     'name'=>'Gold',     'color'=>'#f59e0b', 'threshold'=>500,  'label'=>'$500+'],
                        ['id'=>'platinum', 'name'=>'Platinum', 'color'=>'#a78bfa', 'threshold'=>1000, 'label'=>'$1,000+'],
                        ['id'=>'diamond',  'name'=>'Diamond',  'color'=>'#60a5fa', 'threshold'=>5000, 'label'=>'$5,000+'],
                    ];
                    $tierDescriptions = [
                        'bronze'   => ['Solid foundation.', 'Your journey begins.'],
                        'silver'   => ['Building momentum.', 'Keep going.'],
                        'gold'     => ['Rising above.', "You're doing great."],
                        'platinum' => ['Elite status achieved.', "You're almost unstoppable."],
                        'diamond'  => ['The pinnacle of excellence.', "You're in a league of your own."],
                    ];
                    // Find next tier
                    $nextTier = null;
                    $prevThreshold = 0;
                    foreach($allTiers as $idx => $t) {
                        if($usdSpent < $t['threshold']) { $nextTier = $t; $prevThreshold = $allTiers[$idx-1]['threshold'] ?? 0; break; }
                    }
                @endphp

                {{-- Tier Cards --}}
                <div style="display: flex; gap: 12px; flex-wrap: wrap; justify-content: center; margin-bottom: 28px;">
                    @foreach($allTiers as $t)
                    @php
                        $isActive  = $tier['id'] === $t['id'];
                        $isUnlocked = $usdSpent >= $t['threshold'];
                        $desc = $tierDescriptions[$t['id']];
                        $animated = in_array($t['id'], ['platinum','diamond']);
                    @endphp
                    <div style="
                        flex: 1; min-width: 100px; max-width: 140px;
                        background: {{ $isActive ? 'rgba(255,255,255,0.05)' : 'rgba(255,255,255,0.02)' }};
                        border: 1.5px solid {{ $isActive ? $t['color'] : 'rgba(255,255,255,0.08)' }};
                        border-radius: 14px; padding: 18px 12px 14px;
                        text-align: center; position: relative;
                        box-shadow: {{ $isActive ? '0 0 20px '.$t['color'].'40, inset 0 0 12px '.$t['color'].'10' : 'none' }};
                        transition: all 0.3s;
                        opacity: {{ $isUnlocked ? '1' : '0.45' }};
                    ">
                        @if($isActive)
                        <div style="position:absolute; top:-10px; left:50%; transform:translateX(-50%); background: {{ $t['color'] }}; color: #000; font-size:0.6rem; font-weight:800; padding: 2px 10px; border-radius:20px; white-space:nowrap; letter-spacing:0.5px;">YOU ARE HERE</div>
                        @endif
                        @if($animated)
                        <div style="font-size:0.55rem; background: rgba(255,255,255,0.12); color:{{ $t['color'] }}; padding:1px 7px; border-radius:10px; display:inline-block; margin-bottom:6px; font-weight:700; letter-spacing:0.5px;">ANIMATED</div>
                        @endif
                        {{-- Tier badge image --}}
                        <div style="height: 56px; display:flex; align-items:center; justify-content:center; margin-bottom: 8px;">
                            <img src="{{ asset('images/tiers/icon_'.$t['id'].'.png') }}" alt="{{ $t['name'] }}"
                                 style="height: 52px; object-fit: contain;
                                        filter: {{ $isUnlocked ? 'drop-shadow(0 0 8px '.$t['color'].'80)' : 'grayscale(1) brightness(0.5)' }};
                                        {{ $isActive ? 'animation: tierBadgeBob 2s ease-in-out infinite;' : '' }}">
                        </div>
                        <div style="font-weight: 800; font-size: 0.8rem; color: {{ $isUnlocked ? $t['color'] : '#555' }}; letter-spacing: 1px; margin-bottom: 6px;">{{ strtoupper($t['name']) }}</div>
                        <div style="font-size: 0.75rem; background: rgba(0,0,0,0.25); color: {{ $isUnlocked ? 'rgba(255,255,255,0.7)' : '#444' }}; border: 1px solid {{ $isUnlocked ? 'rgba(255,255,255,0.1)' : 'rgba(255,255,255,0.04)' }}; border-radius: 8px; padding: 3px 0; margin-bottom: 10px;">{{ $t['label'] }}</div>
                        <div style="font-size:0.7rem; color: {{ $isUnlocked ? 'rgba(255,255,255,0.55)' : '#444' }}; line-height:1.5;">{{ $desc[0] }}<br>{{ $desc[1] }}</div>
                    </div>
                    @endforeach
                </div>

                {{-- Progress bar toward next tier --}}
                @if($nextTier)
                @php
                    $range    = $nextTier['threshold'] - $prevThreshold;
                    $progress = min(100, max(0, (($usdSpent - $prevThreshold) / $range) * 100));
                @endphp
                <div style="background: rgba(0,0,0,0.25); border: 1px solid var(--border-color); border-radius: 12px; padding: 16px 20px;">
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 10px;">
                        <span style="font-size:0.8rem; color: var(--text-muted);">Progress to <strong style="color:{{ $nextTier['color'] }};">{{ $nextTier['name'] }}</strong></span>
                        <span style="font-size:0.8rem; color: var(--text-muted);">
                            ${{ number_format($usdSpent, 0) }} / ${{ number_format($nextTier['threshold'], 0) }}
                        </span>
                    </div>
                    <div style="height: 8px; background: rgba(255,255,255,0.06); border-radius: 99px; overflow: hidden;">
                        <div style="height: 100%; width: {{ $progress }}%; background: linear-gradient(90deg, {{ $tier['color'] }}, {{ $nextTier['color'] }}); border-radius: 99px; transition: width 1s ease; box-shadow: 0 0 8px {{ $nextTier['color'] }}80;"></div>
                    </div>
                    <p style="font-size:0.72rem; color: var(--text-muted); margin: 8px 0 0; text-align:right;">
                        ${{ number_format(max(0, $nextTier['threshold'] - $usdSpent), 0) }} more to reach {{ $nextTier['name'] }}
                    </p>
                </div>
                @else
                <div style="text-align:center; padding: 12px; background: rgba(96,165,250,0.07); border: 1px solid rgba(96,165,250,0.25); border-radius: 12px;">
                    <span style="font-size: 0.9rem; color: #60a5fa; font-weight: 700;">💎 Maximum tier achieved — Diamond Elite</span>
                </div>
                @endif
            </div>
            {{-- ─── END MEMBERSHIP TIERS ─── --}}

        </div>
    </div>
</div>


<!-- Upload Banner Modal -->
@if(auth()->check() && (auth()->id() === $user->id || auth()->user()->role === 'admin'))
<div id="bannerModal">
    <div class="modal-box">
        <h3 style="margin-top: 0;">Upload Profile Banner</h3>
        <form action="{{ route('profile.update_banner', ['name' => strtolower($user->name), 'id' => $user->id]) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="form-group">
                <label style="display:block; margin-bottom: 8px; color: var(--text-muted);">Choose Image (Max 5MB)</label>
                <input type="file" name="banner" class="form-control" accept="image/*" required>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn" onclick="document.getElementById('bannerModal').style.display='none'">Cancel</button>
                <button type="submit" class="btn" style="background: var(--primary); color: #000;">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Avatar & Frame Selection Modal -->
<div id="avatarModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.8); z-index: 9999; align-items: center; justify-content: center; backdrop-filter: blur(8px);">
    <div style="background: var(--bg-card); padding: 32px; border-radius: 16px; border: 1px solid var(--border-color); width: 800px; max-width: 95%; position: relative;">
        <h2 style="margin-top: 0; margin-bottom: 24px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">Edit Profile Picture & Frame</h2>

        <div style="display: flex; gap: 40px; flex-wrap: wrap;">
            <!-- Left Side: Cropper -->
            <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column; align-items: center;">
                <h3 style="margin-top: 0; color: var(--text-muted); font-size: 0.9rem;">Profile Picture</h3>
                <div id="croppie-container" style="width: 250px; min-height: 290px; margin-bottom: 8px; position: relative;"></div>

                <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                    <button class="btn" style="padding: 6px 14px; font-size: 1rem; font-weight: bold;" onclick="rotateAvatar(-90)" title="Rotate Left">↺</button>
                    <button class="btn" style="padding: 6px 14px; font-size: 1rem; font-weight: bold;" onclick="rotateAvatar(90)" title="Rotate Right">↻</button>
                    <button class="btn" style="padding: 6px 14px; font-size: 1rem; font-weight: bold;" onclick="flipAvatar('h')" title="Flip Horizontal">↔</button>
                    <button class="btn" style="padding: 6px 14px; font-size: 1rem; font-weight: bold;" onclick="flipAvatar('v')" title="Flip Vertical">↕</button>
                </div>

                <input type="file" id="upload-avatar" accept="image/*" style="display: none;">
                <button class="btn" onclick="document.getElementById('upload-avatar').click()" style="margin-bottom: 16px;">Choose Image</button>
            </div>

            <!-- Right Side: Frames -->
            <div style="flex: 1; min-width: 300px; border-left: 1px solid var(--border-color); padding-left: 40px;">
                <h3 style="margin-top: 0; color: var(--text-muted); font-size: 0.9rem;">Unlocked Frames</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); gap: 16px; max-height: 250px; overflow-y: auto; padding-right: 12px;">
                    <div class="frame-option" data-id="none" style="border: 2px solid var(--border-color); border-radius: 8px; padding: 16px 12px; text-align: center; cursor: pointer; transition: 0.2s;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: #333; margin: 0 auto 12px;"></div>
                        <span style="font-size: 0.8rem; font-weight: 600;">None</span>
                    </div>
                    @foreach($unlockedFrames as $f)
                    <div class="frame-option" data-id="{{ $f['id'] }}" style="border: 2px solid var(--border-color); border-radius: 8px; padding: 16px 12px; text-align: center; cursor: pointer; transition: 0.2s;">
                        <div style="width: 48px; height: 48px; position: relative; margin: 0 auto 12px;">
                            <img src="{{ asset('images/tiers/frame_' . $f['id'] . '.png') }}" alt="{{ $f['name'] }} Frame" style="width: 100%; height: 100%; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%) scale(1.55); pointer-events: none; z-index: 10; object-fit: contain;">
                            <div style="width: 100%; height: 100%; border-radius: 50%; background: #2a2a2d; position: relative; z-index: 1;"></div>
                        </div>
                        <span style="font-size: 0.8rem; font-weight: 600; color: {{ $f['color'] }}">{{ $f['name'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div style="display: flex; justify-content: flex-end; gap: 12px; margin-top: 32px; border-top: 1px solid var(--border-color); padding-top: 24px;">
            <button class="btn" onclick="closeAvatarModal()">Cancel</button>
            <button class="btn" style="background: var(--primary); color: white;" onclick="saveAvatarAndFrame()">Save Changes</button>
        </div>
    </div>
</div>
@endif

    <footer>
        <div style="margin-bottom: 12px;">
            <a href="/" style="color: var(--text-muted); text-decoration: none; margin: 0 10px; font-size: 0.9rem; transition: 0.3s;">Terms of Service</a>
            |
            <a href="/" style="color: var(--text-muted); text-decoration: none; margin: 0 10px; font-size: 0.9rem; transition: 0.3s;">Privacy Policy</a>
        </div>
        &copy; {{ date('Y') }} ABUSER STORE. All Rights Reserved.
    </footer>

    <script>
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

        // === TAB SYSTEM ===
        const isOwnerOrAdmin = {{ (auth()->check() && (auth()->id() === $user->id || auth()->user()->role === 'admin')) ? 'true' : 'false' }};

        function showTab(tabId, el) {
            document.querySelectorAll('.profile-content').forEach(c => c.style.display = 'none');
            document.getElementById('tab-' + tabId).style.display = 'flex';
            document.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
            el.classList.add('active');
        }

        // Set correct default visible tab on load
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.profile-content').forEach(c => c.style.display = 'none');
            // If arriving from a tx_status filter or #transactions hash, open transactions tab
            const hasTxFilter = new URLSearchParams(window.location.search).has('tx_status');
            const hasHash = window.location.hash === '#transactions';
            if (isOwnerOrAdmin) {
                const trxTab = document.getElementById('tab-transactions');
                if (trxTab) trxTab.style.display = 'flex';
                if (hasTxFilter || hasHash) {
                    document.querySelectorAll('.tab-link').forEach(t => t.classList.remove('active'));
                    document.querySelector('.tab-link[onclick*="transactions"]')?.classList.add('active');
                }
            } else {
                const aboutTab = document.getElementById('tab-about');
                if (aboutTab) aboutTab.style.display = 'flex';
            }
        });

        // Copy to clipboard helper (for voucher codes)
        function copyToClipboard(text) {
            if (!text || text.length < 3) return; // Skip if still blurred/empty
            navigator.clipboard?.writeText(text).catch(() => {});
        }

        // === ABOUT EDIT TOGGLE ===
        function toggleAboutEdit(show) {
            const readView = document.getElementById('about-read-view');
            const editView = document.getElementById('about-edit-view');
            if (!readView || !editView) return;
            if (show) {
                readView.style.display = 'none';
                editView.style.display = 'block';
                const ta = document.getElementById('bioTextarea');
                if (ta) updateBioCount(ta);
            } else {
                editView.style.display = 'none';
                readView.style.display = 'block';
                document.getElementById('emojiPanel').style.display = 'none';
            }
        }

        // === BIO FORMATTING ===
        function bioFormat(type) {
            const ta = document.getElementById('bioTextarea');
            if (!ta) return;
            const start = ta.selectionStart, end = ta.selectionEnd;
            const selected = ta.value.substring(start, end) || 'text';
            let wrapped = '';
            if (type === 'bold')   wrapped = `**${selected}**`;
            if (type === 'italic') wrapped = `*${selected}*`;
            if (type === 'strike') wrapped = `~~${selected}~~`;
            ta.value = ta.value.substring(0, start) + wrapped + ta.value.substring(end);
            // Re-position cursor inside the markers
            const cursorPos = start + wrapped.length - (type === 'bold' ? 2 : type === 'italic' ? 1 : 2);
            ta.selectionStart = selected === 'text' ? start + (type === 'bold' ? 2 : type === 'italic' ? 1 : 2) : start + wrapped.length;
            ta.selectionEnd   = ta.selectionStart;
            ta.focus();
            updateBioCount(ta);
        }

        function bioEmojiPicker() {
            const panel = document.getElementById('emojiPanel');
            panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
        }

        function insertEmoji(emoji) {
            const ta = document.getElementById('bioTextarea');
            if (!ta) return;
            const start = ta.selectionStart;
            ta.value = ta.value.substring(0, start) + emoji + ta.value.substring(start);
            ta.selectionStart = ta.selectionEnd = start + emoji.length;
            ta.focus();
            document.getElementById('emojiPanel').style.display = 'none';
            updateBioCount(ta);
        }

        function updateBioCount(ta) {
            const el = document.getElementById('bioCharCount');
            if (el) el.textContent = ta.value.length;
        }

        // Init char count on page load
        document.addEventListener('DOMContentLoaded', () => {
            const ta = document.getElementById('bioTextarea');
            if (ta) updateBioCount(ta);
        });

        // === THEME TOGGLE ===
        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark-mode');
            const icon = document.getElementById('themeIcon');
            if(icon) {
                if (isDark) {
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />';
                } else {
                    icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />';
                }
            }
        }
        function toggleTheme() {
            document.documentElement.classList.toggle('dark-mode');
            localStorage.setItem('abuser_theme', document.documentElement.classList.contains('dark-mode') ? 'dark' : 'light');
        }

        // === AVATAR MODAL & CROPPER ===
        let croppieInstance = null;
        let selectedFrame = '{{ $user->equipped_frame ?? "none" }}';
        let currentAvatarSrc = '';

        function openAvatarModal() {
            document.getElementById('avatarModal').style.display = 'flex';

            setTimeout(() => {
                if (!croppieInstance) {
                    croppieInstance = new Croppie(document.getElementById('croppie-container'), {
                        viewport: { width: 150, height: 150, type: 'circle' },
                        boundary: { width: 250, height: 250 },
                        showZoomer: true,
                        enableOrientation: true,
                        enableExif: true
                    });
                }
                currentAvatarSrc = document.querySelector('.profile-avatar').src;
                croppieInstance.bind({
                    url: currentAvatarSrc
                }).then(() => {
                    croppieInstance.setZoom(0);
                });
            }, 100);
            updateFrameSelectionUI();
        }

        function rotateAvatar(deg) {
            if (croppieInstance) {
                croppieInstance.rotate(deg);
            }
        }

        function flipAvatar(dir) {
            if (!currentAvatarSrc || !croppieInstance) return;
            const img = new Image();
            img.crossOrigin = "Anonymous";
            img.onload = function() {
                const canvas = document.createElement('canvas');
                canvas.width = img.width;
                canvas.height = img.height;
                const ctx = canvas.getContext('2d');
                if (dir === 'h') {
                    ctx.translate(canvas.width, 0);
                    ctx.scale(-1, 1);
                } else {
                    ctx.translate(0, canvas.height);
                    ctx.scale(1, -1);
                }
                ctx.drawImage(img, 0, 0);
                currentAvatarSrc = canvas.toDataURL('image/png');
                croppieInstance.bind({ url: currentAvatarSrc });
            };
            img.src = currentAvatarSrc;
        }

        function closeAvatarModal() {
            document.getElementById('avatarModal').style.display = 'none';
        }

        document.getElementById('upload-avatar')?.addEventListener('change', function(e) {
            if (e.target.files && e.target.files[0]) {
                let reader = new FileReader();
                reader.onload = function(ev) {
                    currentAvatarSrc = ev.target.result;
                    croppieInstance.bind({ url: currentAvatarSrc }).then(() => {
                        croppieInstance.setZoom(0);
                    });
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        });

        document.querySelectorAll('.frame-option').forEach(el => {
            el.addEventListener('click', function() {
                selectedFrame = this.getAttribute('data-id');
                updateFrameSelectionUI();
            });
        });

        function updateFrameSelectionUI() {
            document.querySelectorAll('.frame-option').forEach(el => {
                if (el.getAttribute('data-id') === selectedFrame) {
                    el.style.borderColor = 'var(--primary)';
                    el.style.background = 'rgba(79,70,229,0.1)';
                } else {
                    el.style.borderColor = 'var(--border-color)';
                    el.style.background = 'transparent';
                }
            });
        }

        function saveAvatarAndFrame() {
            const btn = document.querySelector('#avatarModal .btn[style*="var(--primary)"]');
            btn.innerText = 'Saving...';
            btn.disabled = true;

            croppieInstance.result({ type: 'base64', size: 'viewport', format: 'png' }).then(function(base64) {
                fetch('{{ route("profile.update_avatar", ["name" => strtolower($user->name), "id" => $user->id]) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        avatar_base64: base64,
                        equipped_frame: selectedFrame
                    })
                }).then(res => res.json()).then(data => {
                    if (data.success) {
                        window.location.reload();
                    } else {
                        alert('Error: ' + data.message);
                        btn.innerText = 'Save Changes';
                        btn.disabled = false;
                    }
                }).catch(err => {
                    alert('Request failed');
                    btn.innerText = 'Save Changes';
                    btn.disabled = false;
                });
            });
        }
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/croppie/2.6.5/croppie.min.js"></script>
</body>
</html>
