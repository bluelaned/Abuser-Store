@extends('admin.layouts.app')

@section('title', 'Activity Log')

@section('content')

<div class="header-actions">
    <h1>📋 Activity Log</h1>
    <div style="font-size:0.875rem;color:var(--text-muted);">Total: {{ $logs->total() }} entries</div>
</div>

{{-- Filter --}}
<div class="card" style="padding:18px 20px;margin-bottom:0;border-bottom-left-radius:0;border-bottom-right-radius:0;border-bottom:none;">
    <form method="GET" action="{{ route('admin.activity_logs') }}" style="display:flex;flex-wrap:wrap;gap:10px;align-items:flex-end;">
        <div style="flex:1;min-width:200px;">
            <label style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Description, entity type..." class="form-control">
        </div>
        <div style="min-width:130px;">
            <label style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">Action</label>
            <select name="action" class="form-control">
                <option value="">All Actions</option>
                @foreach(['created','updated','deleted','exported','truncated','toggled'] as $act)
                    <option value="{{ $act }}" {{ request('action')===$act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                @endforeach
            </select>
        </div>
        <div style="min-width:150px;">
            <label style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">Entity Type</label>
            <select name="entity_type" class="form-control">
                <option value="">All Types</option>
                @foreach(['product','transaction','user','promo','voucher','announcement','review','static_page'] as $et)
                    <option value="{{ $et }}" {{ request('entity_type')===$et ? 'selected' : '' }}>{{ ucfirst($et) }}</option>
                @endforeach
            </select>
        </div>
        <div style="min-width:140px;">
            <label style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        <div style="min-width:140px;">
            <label style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;display:block;margin-bottom:6px;">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>
        <div style="display:flex;gap:8px;align-items:flex-end;padding-bottom:1px;">
            <button type="submit" class="btn btn-primary">Filter</button>
            @if(request()->hasAny(['search','action','entity_type','date_from','date_to']))
                <a href="{{ route('admin.activity_logs') }}" class="btn btn-secondary">Clear</a>
            @endif
        </div>
    </form>
</div>

<div class="card" style="border-top-left-radius:0;border-top-right-radius:0;">
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Admin</th>
                <th>Action</th>
                <th>Entity</th>
                <th>Description</th>
                <th>IP Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse($logs as $log)
            <tr>
                <td style="color:var(--text-muted);font-size:0.8rem;white-space:nowrap;">{{ $log->created_at->format('d M Y H:i') }}</td>
                <td>
                    @if($log->user)
                        <div style="display:flex;align-items:center;gap:8px;">
                            <img src="{{ $log->user->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode($log->user->name).'&background=random' }}" style="width:28px;height:28px;border-radius:50%;border:1px solid var(--border-color);" alt="">
                            <span style="font-weight:600;font-size:0.82rem;">{{ $log->user->name }}</span>
                        </div>
                    @else
                        <span style="color:var(--text-muted);font-size:0.82rem;">System</span>
                    @endif
                </td>
                <td>
                    @php
                        $actionColors = [
                            'created'   => '#22c55e',
                            'updated'   => '#3b82f6',
                            'deleted'   => '#ef4444',
                            'exported'  => '#8b5cf6',
                            'truncated' => '#f97316',
                            'toggled'   => '#06b6d4',
                        ];
                        $color = $actionColors[$log->action] ?? '#94a3b8';
                    @endphp
                    <span style="background:{{ $color }}20;color:{{ $color }};padding:2px 8px;border-radius:20px;font-size:0.72rem;font-weight:700;text-transform:uppercase;">{{ $log->action }}</span>
                </td>
                <td>
                    @if($log->entity_type)
                        <span style="font-size:0.8rem;color:var(--text-muted);">{{ $log->entity_type }}</span>
                        @if($log->entity_id)
                            <span style="font-size:0.75rem;color:var(--text-muted);">#{{ $log->entity_id }}</span>
                        @endif
                    @else
                        <span style="color:var(--text-muted);">—</span>
                    @endif
                </td>
                <td style="font-size:0.82rem;max-width:350px;">{{ $log->description }}</td>
                <td style="font-size:0.78rem;color:var(--text-muted);font-family:monospace;">{{ $log->ip_address ?? '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center;padding:40px;color:var(--text-muted);">No activity logs yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($logs->hasPages())
    <div style="padding:16px 0 4px;display:flex;justify-content:flex-end;">
        {{ $logs->links() }}
    </div>
    @endif
</div>

@endsection
