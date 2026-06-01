@extends('admin.layouts.app')

@section('title', 'Static Pages')

@section('content')

<div class="header-actions">
    <h1>📄 Static Pages</h1>
    <div style="font-size:0.875rem;color:var(--text-muted);">Manage Terms of Service & Privacy Policy content</div>
</div>

@if(session('success'))
    <div class="alert alert-success">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ session('success') }}
    </div>
@endif

{{-- Tabs --}}
<div style="display:flex;gap:8px;margin-bottom:24px;border-bottom:2px solid var(--border-color);padding-bottom:0;">
    <button onclick="switchTab('tos')" id="tab-tos" style="padding:10px 20px;border:none;background:none;cursor:pointer;font-weight:600;font-size:0.875rem;border-bottom:2px solid var(--primary);margin-bottom:-2px;color:var(--primary);">
        Terms of Service
    </button>
    <button onclick="switchTab('privacy')" id="tab-privacy" style="padding:10px 20px;border:none;background:none;cursor:pointer;font-weight:600;font-size:0.875rem;border-bottom:2px solid transparent;margin-bottom:-2px;color:var(--text-muted);">
        Privacy Policy
    </button>
</div>

{{-- TOS Tab --}}
<div id="panel-tos">
    <div class="card">
        <div style="margin-bottom:20px;">
            <h2 style="font-size:1.1rem;font-weight:700;">Terms of Service</h2>
            <p style="font-size:0.82rem;color:var(--text-muted);margin-top:4px;">This content will be displayed at <code>/terms</code>. Supports HTML.</p>
        </div>
        <form action="{{ route('admin.static_pages.update', 'tos') }}" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Page Title</label>
                <input type="text" name="title" class="form-control" value="{{ $pages['tos']->title ?? 'Terms of Service' }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Content <span style="color:var(--text-muted);font-size:0.8rem;">(HTML supported)</span></label>
                <textarea name="content" class="form-control" rows="20" style="font-family:monospace;font-size:0.82rem;" required>{{ $pages['tos']->content ?? '' }}</textarea>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                @if(isset($pages['tos']))
                    <span style="font-size:0.78rem;color:var(--text-muted);">Last updated: {{ $pages['tos']->updated_at->format('d M Y H:i') }}</span>
                @else
                    <span style="font-size:0.78rem;color:#f97316;">⚠ Not set yet — page will show default content</span>
                @endif
                <button type="submit" class="btn btn-primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save Terms of Service
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Privacy Tab --}}
<div id="panel-privacy" style="display:none;">
    <div class="card">
        <div style="margin-bottom:20px;">
            <h2 style="font-size:1.1rem;font-weight:700;">Privacy Policy</h2>
            <p style="font-size:0.82rem;color:var(--text-muted);margin-top:4px;">This content will be displayed at <code>/privacy</code>. Supports HTML.</p>
        </div>
        <form action="{{ route('admin.static_pages.update', 'privacy') }}" method="POST">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Page Title</label>
                <input type="text" name="title" class="form-control" value="{{ $pages['privacy']->title ?? 'Privacy Policy' }}" required>
            </div>
            <div class="form-group">
                <label class="form-label">Content <span style="color:var(--text-muted);font-size:0.8rem;">(HTML supported)</span></label>
                <textarea name="content" class="form-control" rows="20" style="font-family:monospace;font-size:0.82rem;" required>{{ $pages['privacy']->content ?? '' }}</textarea>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;">
                @if(isset($pages['privacy']))
                    <span style="font-size:0.78rem;color:var(--text-muted);">Last updated: {{ $pages['privacy']->updated_at->format('d M Y H:i') }}</span>
                @else
                    <span style="font-size:0.78rem;color:#f97316;">⚠ Not set yet — page will show default content</span>
                @endif
                <button type="submit" class="btn btn-primary">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Save Privacy Policy
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function switchTab(tab) {
    document.getElementById('panel-tos').style.display = tab === 'tos' ? 'block' : 'none';
    document.getElementById('panel-privacy').style.display = tab === 'privacy' ? 'block' : 'none';

    const tosBorder = tab === 'tos' ? 'var(--primary)' : 'transparent';
    const privBorder = tab === 'privacy' ? 'var(--primary)' : 'transparent';
    const tosColor = tab === 'tos' ? 'var(--primary)' : 'var(--text-muted)';
    const privColor = tab === 'privacy' ? 'var(--primary)' : 'var(--text-muted)';

    document.getElementById('tab-tos').style.borderBottomColor = tosBorder;
    document.getElementById('tab-tos').style.color = tosColor;
    document.getElementById('tab-privacy').style.borderBottomColor = privBorder;
    document.getElementById('tab-privacy').style.color = privColor;
}

// If URL has #privacy hash, auto-switch
if (window.location.hash === '#privacy') switchTab('privacy');
</script>

@endsection
