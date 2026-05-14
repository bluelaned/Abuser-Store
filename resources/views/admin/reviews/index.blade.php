@extends('admin.layouts.app')

@section('title', 'Manage Reviews')

@section('content')

<div class="header-actions">
    <h1>
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="vertical-align:middle; margin-right:8px; color:var(--primary);">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
        </svg>
        Manage Reviews
    </h1>
    <div style="display:flex; gap:10px; align-items:center;">
        <div style="background:var(--bg-body); border:1px solid var(--border-color); border-radius:8px; padding:8px 16px; font-size:0.875rem; color:var(--text-muted);">
            Total: <strong style="color:var(--text-main);">{{ $reviews->count() }}</strong> &nbsp;|&nbsp;
            Published: <strong style="color:var(--success);">{{ $reviews->where('is_published', true)->count() }}</strong> &nbsp;|&nbsp;
            Pending: <strong style="color:#b45309;">{{ $reviews->where('is_published', false)->count() }}</strong>
        </div>
    </div>
</div>

<div class="card" style="padding:0; overflow:hidden;">
    <table>
        <thead>
            <tr>
                <th style="width:200px;">User</th>
                <th style="width:120px;">Rating</th>
                <th>Comment</th>
                <th style="width:110px;">Status</th>
                <th style="width:110px;">Date</th>
                <th style="width:160px;">Actions</th>
            </tr>
        </thead>
        <tbody id="skeletonRows">
            @for($s = 0; $s < 4; $s++)
            <tr class="sk-tr">
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div class="skeleton" style="width:36px;height:36px;border-radius:50%;flex-shrink:0;"></div>
                        <div class="skeleton sk-cell w80"></div>
                    </div>
                </td>
                <td><div class="skeleton sk-cell w50"></div></td>
                <td><div class="skeleton sk-cell w80" style="margin-bottom:6px;"></div><div class="skeleton sk-cell w50"></div></td>
                <td><div class="skeleton sk-cell w50"></div></td>
                <td><div class="skeleton sk-cell w80"></div></td>
                <td><div class="skeleton sk-cell w80"></div></td>
            </tr>
            @endfor
        </tbody>
        <tbody id="realRows" style="display:none;">
            @forelse($reviews as $review)
            <tr>
                {{-- User --}}
                <td>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <img src="{{ $review->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($review->user->name ?? 'A').'&background=6366f1&color=fff' }}"
                             alt="Avatar"
                             style="width:36px;height:36px;border-radius:50%;border:1px solid var(--border-color);object-fit:cover;flex-shrink:0;">
                        <div>
                            <div style="font-weight:600;font-size:0.875rem;color:var(--text-main);">{{ $review->user->name ?? 'Unknown' }}</div>
                            @if($review->user && $review->user->provider_name === 'discord')
                                <div style="font-size:0.7rem;color:#5865F2;font-weight:700;">DISCORD</div>
                            @endif
                        </div>
                    </div>
                </td>

                {{-- Rating --}}
                <td>
                    <div style="display:flex;flex-direction:column;gap:2px;">
                        <div style="font-size:1rem;line-height:1;letter-spacing:1px;">
                            @for($i = 1; $i <= 5; $i++)
                                <span style="color: {{ $i <= $review->rating ? '#FFD700' : 'var(--border-color)' }};">★</span>
                            @endfor
                        </div>
                        <div style="font-size:0.7rem;color:var(--text-muted);font-weight:600;">{{ $review->rating }}/5</div>
                    </div>
                </td>

                {{-- Comment --}}
                <td>
                    <div style="font-size:0.875rem;color:var(--text-main);line-height:1.5;max-width:360px;">
                        "{{ \Illuminate\Support\Str::limit($review->comment, 120) }}"
                    </div>
                </td>

                {{-- Status --}}
                <td>
                    @if($review->is_published)
                        <span class="badge badge-success">Published</span>
                    @else
                        <span class="badge badge-warning">Pending</span>
                    @endif
                </td>

                {{-- Date --}}
                <td style="color:var(--text-muted);font-size:0.8rem;white-space:nowrap;">
                    {{ $review->created_at->format('d M Y') }}
                </td>

                {{-- Actions --}}
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <form action="{{ route('admin.reviews.toggle_publish', $review->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('PATCH')
                            @if($review->is_published)
                                <button type="submit" class="btn btn-sm btn-secondary" title="Hide Review">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                                    Hide
                                </button>
                            @else
                                <button type="submit" class="btn btn-sm btn-primary" title="Publish Review">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Publish
                                </button>
                            @endif
                        </form>
                        <form action="{{ route('admin.reviews.destroy', $review->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this review?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" title="Delete Review">
                                <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Delete
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding:60px 20px; color:var(--text-muted);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display:block;margin:0 auto 12px;opacity:0.3;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                    No reviews yet. They will appear here once customers submit them.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<style>
    /* Skeleton animation reuse */
    .skeleton { background: var(--border-color); border-radius: 4px; animation: skPulse 1.4s ease-in-out infinite; }
    @keyframes skPulse { 0%,100%{opacity:1} 50%{opacity:0.4} }
    .sk-cell { height: 14px; }
    .w30  { width: 30%; }
    .w50  { width: 50%; }
    .w80  { width: 80%; }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const sk   = document.getElementById('skeletonRows');
        const real = document.getElementById('realRows');
        setTimeout(() => {
            sk.style.transition = 'opacity 0.3s ease';
            sk.style.opacity = '0';
            setTimeout(() => {
                sk.style.display = 'none';
                real.style.display = '';
                real.style.opacity = '0';
                real.style.transition = 'opacity 0.4s ease';
                requestAnimationFrame(() => requestAnimationFrame(() => real.style.opacity = '1'));
            }, 300);
        }, 400);
    });
</script>

@endsection
