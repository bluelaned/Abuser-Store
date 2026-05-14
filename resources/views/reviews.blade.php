<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Reviews | ABUSER STORE</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/index.css') }}">
    <style>
        /* ── Page Layout ── */
        .rv-page { max-width: 1200px; margin: 0 auto; padding: 40px 24px 80px; }

        /* ── Back link ── */
        .rv-back {
            display: inline-flex; align-items: center; gap: 7px;
            color: var(--text-muted); font-size: 0.85rem; font-weight: 600;
            text-decoration: none; margin-bottom: 32px;
            transition: color 0.2s;
        }
        .rv-back:hover { color: var(--primary); }

        /* ── Hero / Stats banner ── */
        .rv-hero {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 36px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 32px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }
        .rv-hero-left h1 {
            font-size: 2rem; font-weight: 900;
            color: var(--text-main); margin: 0 0 4px 0;
        }
        .rv-hero-left p { color: var(--text-muted); font-size: 0.9rem; margin: 0; }

        .rv-avg-score {
            font-size: 4rem; font-weight: 900;
            color: var(--text-main); line-height: 1;
        }
        .rv-avg-stars { font-size: 1.6rem; color: #FFD700; letter-spacing: 2px; margin: 4px 0; }
        .rv-avg-label { font-size: 0.78rem; color: var(--text-muted); font-weight: 600; }

        /* Rating bars */
        .rv-bars { display: flex; flex-direction: column; gap: 6px; min-width: 200px; }
        .rv-bar-row { display: flex; align-items: center; gap: 10px; font-size: 0.78rem; }
        .rv-bar-label { color: var(--text-muted); font-weight: 600; width: 40px; flex-shrink: 0; }
        .rv-bar-track {
            flex: 1; height: 8px; background: var(--border-color);
            border-radius: 4px; overflow: hidden;
        }
        .rv-bar-fill { height: 100%; border-radius: 4px; background: #FFD700; transition: width 0.6s ease; }
        .rv-bar-count { color: var(--text-muted); font-weight: 600; width: 28px; text-align: right; flex-shrink: 0; }

        /* ── Toolbar (sort + filter) ── */
        .rv-toolbar {
            display: flex; align-items: center; justify-content: space-between;
            gap: 16px; flex-wrap: wrap; margin-bottom: 24px;
        }
        .rv-total-label {
            font-size: 0.875rem; font-weight: 600; color: var(--text-muted);
        }
        .rv-total-label strong { color: var(--text-main); }

        .rv-controls { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }

        /* Star filter pills */
        .rv-star-pills { display: flex; gap: 6px; }
        .rv-star-pill {
            padding: 5px 12px; border-radius: 20px; font-size: 0.78rem;
            font-weight: 700; border: 1px solid var(--border-color);
            background: var(--bg-surface); color: var(--text-muted);
            text-decoration: none; transition: all 0.18s; cursor: pointer;
            white-space: nowrap;
        }
        .rv-star-pill:hover, .rv-star-pill.active {
            border-color: var(--primary); color: var(--primary);
            background: rgba(59,130,246,0.08);
        }
        .rv-star-pill.active { font-weight: 800; }

        /* Sort select */
        .rv-sort-select {
            padding: 7px 12px; border-radius: 8px;
            border: 1px solid var(--border-color);
            background: var(--bg-surface); color: var(--text-main);
            font-size: 0.82rem; font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer; outline: none; transition: border-color 0.2s;
        }
        .rv-sort-select:focus { border-color: var(--primary); }

        /* ── Grid ── */
        .rv-grid-page {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
            gap: 16px;
        }
        .rv-card {
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: 14px; padding: 18px 20px;
            display: flex; flex-direction: column; gap: 12px;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
        }
        .rv-card:hover {
            border-color: var(--primary);
            box-shadow: 0 6px 20px rgba(59,130,246,0.1);
            transform: translateY(-2px);
        }
        .rv-card-top { display: flex; align-items: flex-start; gap: 12px; }
        .rv-avatar {
            width: 42px; height: 42px; border-radius: 50%;
            object-fit: cover; border: 2px solid var(--border-color); flex-shrink: 0;
        }
        .rv-user-info { flex: 1; min-width: 0; }
        .rv-username {
            font-weight: 700; font-size: 0.9rem; color: var(--text-main);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .rv-stars-row { display: flex; align-items: center; gap: 2px; font-size: 0.9rem; margin-top: 3px; }
        .rv-rating-num { font-size: 0.7rem; color: var(--text-muted); font-weight: 600; margin-left: 4px; }
        .rv-date { font-size: 0.7rem; color: var(--text-muted); white-space: nowrap; flex-shrink: 0; padding-top: 2px; }
        .rv-comment {
            font-size: 0.84rem; color: var(--text-muted);
            line-height: 1.6; margin: 0;
            display: -webkit-box; -webkit-line-clamp: 4;
            -webkit-box-orient: vertical; overflow: hidden;
        }
        /* Rating badge on card */
        .rv-rating-badge {
            align-self: flex-start; padding: 3px 10px; border-radius: 20px;
            font-size: 0.7rem; font-weight: 800;
        }
        .rv-r5 { background: rgba(16,185,129,0.12); color: #047857; }
        .rv-r4 { background: rgba(59,130,246,0.12); color: #1d4ed8; }
        .rv-r3 { background: rgba(245,158,11,0.12); color: #b45309; }
        .rv-r2 { background: rgba(249,115,22,0.12); color: #c2410c; }
        .rv-r1 { background: rgba(239,68,68,0.12); color: #b91c1c; }

        /* ── Pagination ── */
        .rv-pagination {
            display: flex; justify-content: center; gap: 8px;
            margin-top: 40px; flex-wrap: wrap;
        }
        .rv-pagination a, .rv-pagination span {
            padding: 8px 14px; border-radius: 8px; font-size: 0.82rem; font-weight: 600;
            border: 1px solid var(--border-color); text-decoration: none;
            color: var(--text-muted); background: var(--bg-surface); transition: all 0.18s;
        }
        .rv-pagination a:hover { border-color: var(--primary); color: var(--primary); }
        .rv-pagination .rv-page-active {
            background: var(--primary); color: #fff; border-color: var(--primary);
        }

        /* ── Empty state ── */
        .rv-empty-state {
            text-align: center; padding: 80px 20px;
            color: var(--text-muted); font-size: 0.9rem;
        }
        .rv-empty-state svg { margin: 0 auto 16px; display: block; opacity: 0.25; }

        @media (max-width: 768px) {
            .rv-hero { padding: 24px; flex-direction: column; align-items: flex-start; }
            .rv-avg-score { font-size: 3rem; }
            .rv-grid-page { grid-template-columns: 1fr 1fr; }
        }
        @media (max-width: 480px) {
            .rv-grid-page { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <script>
        if (localStorage.getItem('abuser_theme') !== 'light') {
            document.documentElement.classList.add('dark-mode');
        }
    </script>

    {{-- NAVBAR --}}
    <nav class="navbar">
        <div style="display:flex; align-items:center; gap:16px;">
            <a href="/" class="brand">ABUSER <span>STORE</span></a>
            <a href="{{ route('reviews.index') }}" class="reviews-nav-btn" style="border-color:var(--primary);color:var(--primary);">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Reviews
            </a>
        </div>
        <div style="display:flex; align-items:center; gap:16px;">

            <button onclick="toggleTheme()" style="background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px; display:flex;">
                <svg id="themeIcon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
            </button>
        </div>
    </nav>

    <div class="rv-page">

        {{-- BACK LINK --}}
        <a href="/" class="rv-back">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Back to Shop
        </a>

        {{-- HERO STATS --}}
        <div class="rv-hero">
            <div class="rv-hero-left">
                <h1>Customer Reviews</h1>
                <p>Real feedback from verified ABUSER STORE customers</p>
            </div>

            <div style="text-align:center;">
                <div class="rv-avg-score">{{ number_format($avgRating, 1) }}</div>
                <div class="rv-avg-stars">
                    @for($i = 1; $i <= 5; $i++)
                        @if($i <= round($avgRating))★@else☆@endif
                    @endfor
                </div>
                <div class="rv-avg-label">Average Rating</div>
            </div>

            <div class="rv-bars">
                @foreach([5,4,3,2,1] as $star)
                @php $cnt = $starCounts[$star] ?? 0; $pct = $totalReviews > 0 ? ($cnt / $totalReviews * 100) : 0; @endphp
                <div class="rv-bar-row">
                    <span class="rv-bar-label">★ {{ $star }}</span>
                    <div class="rv-bar-track"><div class="rv-bar-fill" style="width:{{ $pct }}%"></div></div>
                    <span class="rv-bar-count">{{ $cnt }}</span>
                </div>
                @endforeach
            </div>

            <div style="text-align:center;">
                <div style="font-size:2.5rem; font-weight:900; color:var(--text-main);">{{ number_format($totalReviews) }}</div>
                <div style="font-size:0.78rem; color:var(--text-muted); font-weight:600; margin-top:2px;">Total Reviews</div>
            </div>
        </div>

        {{-- LEAVE A REVIEW SECTION --}}
        <div class="review-form-section" style="margin-bottom: 40px;">
            @auth
                @php
                    $hasReviewed = \App\Models\Review::where('user_id', auth()->id())->exists();
                @endphp
                @if(!$hasReviewed)
                <div class="review-form-card" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 20px; padding: 36px 40px;">
                    <div class="review-form-header" style="display: flex; gap: 16px; align-items: flex-start; margin-bottom: 24px;">
                        <div class="review-form-icon" style="color: var(--primary); background: var(--bg-active); padding: 12px; border-radius: 12px;">
                            <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </div>
                        <div>
                            <div class="review-form-title" style="font-size: 1.25rem; font-weight: 800; color: var(--text-main);">Leave a Review</div>
                            <div class="review-form-subtitle" style="font-size: 0.9rem; color: var(--text-muted); margin-top: 4px;">Share your experience with ABUSER STORE</div>
                        </div>
                    </div>
                    <form action="{{ route('reviews.store') }}" method="POST" class="review-form-body" style="display: flex; flex-direction: column; gap: 20px;">
                        @csrf
                        <div class="star-rating-group">
                            <label class="review-label" style="display: block; font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Your Rating</label>
                            <div style="display: flex; align-items: center; gap: 16px;">
                                <div class="star-picker" id="starPicker">
                                    @for($i = 5; $i >= 1; $i--)
                                    <input type="radio" name="rating" id="star{{ $i }}" value="{{ $i }}" {{ $i == 5 ? 'checked' : '' }}>
                                    <label for="star{{ $i }}" title="{{ $i }} stars">★</label>
                                    @endfor
                                </div>
                                <span class="star-label-text" id="starLabelText" style="font-size: 0.9rem; font-weight: 600; color: var(--primary);">5 / 5 — Excellent!</span>
                            </div>
                        </div>
                        <div class="review-field-group">
                            <label class="review-label" style="display: block; font-size: 0.9rem; font-weight: 700; color: var(--text-main); margin-bottom: 8px;">Your Comment</label>
                            <textarea name="comment" required rows="3" class="review-textarea" placeholder="Tell us about your experience..." style="width: 100%; padding: 16px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--bg-body); color: var(--text-main); font-family: 'Inter', sans-serif; resize: vertical; min-height: 100px; outline: none; transition: 0.2s;"></textarea>
                        </div>
                        <button type="submit" class="review-submit-btn" style="align-self: flex-start; display: flex; align-items: center; gap: 8px; padding: 12px 24px; border-radius: 10px; border: none; background: var(--primary); color: #fff; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: 0.2s;" onmouseover="this.style.filter='brightness(1.1)'" onmouseout="this.style.filter='brightness(1)'">
                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M22 2L11 13M22 2l-7 20-4-9-9-4 20-7z"/></svg>
                            Submit Review
                        </button>
                    </form>
                </div>
                @endif
            @else
                <div class="review-login-prompt" style="text-align: center; padding: 30px; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 20px; display: flex; flex-direction: column; align-items: center; gap: 12px;">
                    <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="color: var(--text-muted);"><path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    <div style="font-weight: 600; color: var(--text-muted);">
                        <a href="{{ route('auth.discord') }}" style="color: #5865F2; text-decoration: none;">Login with Discord</a> to leave a review
                    </div>
                </div>
            @endauth
        </div>

        {{-- TOOLBAR --}}
        <div class="rv-toolbar">
            <div class="rv-total-label">
                Showing <strong>{{ $reviews->firstItem() }}–{{ $reviews->lastItem() }}</strong> of <strong>{{ $reviews->total() }}</strong> reviews
                @if($filter !== 'all') &nbsp;·&nbsp; Filtered by <strong>{{ $filter }}★</strong> @endif
            </div>
            <div class="rv-controls">
                {{-- Star filter pills --}}
                <div class="rv-star-pills">
                    <a href="{{ route('reviews.index', ['sort'=>$sort, 'rating'=>'all']) }}" class="rv-star-pill {{ $filter==='all' ? 'active' : '' }}">All</a>
                    @foreach([5,4,3,2,1] as $s)
                    <a href="{{ route('reviews.index', ['sort'=>$sort, 'rating'=>$s]) }}" class="rv-star-pill {{ $filter==(string)$s ? 'active' : '' }}">{{ $s }}★</a>
                    @endforeach
                </div>
                {{-- Sort select --}}
                <select class="rv-sort-select" onchange="window.location.href=this.value">
                    @php
                        $sorts = ['newest'=>'Newest First','oldest'=>'Oldest First','highest'=>'Highest Rating','lowest'=>'Lowest Rating'];
                    @endphp
                    @foreach($sorts as $val => $label)
                    <option value="{{ route('reviews.index', ['sort'=>$val, 'rating'=>$filter]) }}" {{ $sort===$val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- GRID --}}
        @if($reviews->count())
        <div class="rv-grid-page">
            @foreach($reviews as $review)
            @php
                $badgeClass = 'rv-r'.$review->rating;
                $badgeLabels = [5=>'Excellent',4=>'Very Good',3=>'Good',2=>'Fair',1=>'Poor'];
            @endphp
            <div class="rv-card">
                <div class="rv-card-top">
                    <img src="{{ $review->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($review->user->name ?? 'A').'&background=6366f1&color=fff' }}"
                         alt="Avatar" class="rv-avatar">
                    <div class="rv-user-info">
                        <div class="rv-username">{{ $review->user->name ?? 'Anonymous' }}</div>
                        <div class="rv-stars-row">
                            @for($i=1;$i<=5;$i++)
                                <span style="color:{{ $i<=$review->rating ? '#FFD700' : 'var(--border-color)' }}">★</span>
                            @endfor
                            <span class="rv-rating-num">{{ $review->rating }}/5</span>
                        </div>
                    </div>
                    <div class="rv-date">{{ $review->created_at->diffForHumans() }}</div>
                </div>
                <p class="rv-comment">"{{ $review->comment }}"</p>
                <span class="rv-rating-badge {{ $badgeClass }}">{{ $badgeLabels[$review->rating] }}</span>
            </div>
            @endforeach
        </div>

        {{-- PAGINATION --}}
        @if($reviews->hasPages())
        <div class="rv-pagination">
            {{-- Prev --}}
            @if($reviews->onFirstPage())
                <span>‹ Prev</span>
            @else
                <a href="{{ $reviews->previousPageUrl() }}">‹ Prev</a>
            @endif

            {{-- Pages --}}
            @foreach($reviews->getUrlRange(max(1,$reviews->currentPage()-2), min($reviews->lastPage(),$reviews->currentPage()+2)) as $page => $url)
                @if($page == $reviews->currentPage())
                    <span class="rv-page-active">{{ $page }}</span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach

            {{-- Next --}}
            @if($reviews->hasMorePages())
                <a href="{{ $reviews->nextPageUrl() }}">Next ›</a>
            @else
                <span>Next ›</span>
            @endif
        </div>
        @endif

        @else
        <div class="rv-empty-state">
            <svg width="56" height="56" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            <p>No reviews found for the selected filter.</p>
            <a href="{{ route('reviews.index') }}" class="reviews-nav-btn" style="margin-top:12px;">Clear Filters</a>
        </div>
        @endif

    </div>

    <footer>
        <div style="margin-bottom:12px;">
            <a href="{{ route('tos') }}" style="color:var(--text-muted); text-decoration:none; margin:0 10px; font-size:0.9rem;">Terms of Service</a>
            | <a href="{{ route('privacy') }}" style="color:var(--text-muted); text-decoration:none; margin:0 10px; font-size:0.9rem;">Privacy Policy</a>
        </div>
        &copy; {{ date('Y') }} ABUSER STORE. All Rights Reserved.
    </footer>

    <script>
        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark-mode');
            const icon = document.getElementById('themeIcon');
            if (isDark) {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>';
            } else {
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>';
            }
        }
        function toggleTheme() {
            document.documentElement.classList.toggle('dark-mode');
            localStorage.setItem('abuser_theme', document.documentElement.classList.contains('dark-mode') ? 'dark' : 'light');
            updateThemeIcon();
        }
        document.addEventListener('DOMContentLoaded', updateThemeIcon);
    </script>
</body>
</html>
