@extends('admin.layouts.app')

@section('title', 'Active Users')

@section('content')
<style>
    /* Modal Edit Email */
    .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.35); z-index: 999; align-items: center; justify-content: center; }
    .modal-content { background: var(--bg-card); padding: 32px; border-radius: 12px; width: 400px; max-width: 90vw; border: 1px solid var(--border-color); box-shadow: 0 10px 25px rgba(0,0,0,0.3); animation: modalSlideIn 0.3s cubic-bezier(0.22,1,0.36,1) both; }

    .status-dot { height: 10px; width: 10px; border-radius: 50%; display: inline-block; margin-right: 6px; }
    .online { background-color: var(--success); box-shadow: 0 0 8px rgba(16, 185, 129, 0.4); }
    .offline { background-color: var(--danger); }

    .stat-card { background: var(--bg-card); border: 1px solid var(--border-color); padding: 24px; border-radius: 12px; display: flex; align-items: center; gap: 16px; min-width: 240px; transition: transform 0.2s ease, box-shadow 0.2s ease; }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
    .stat-icon { width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }

    /* Table Layout Fixes */
    table th, table td { padding: 12px 16px; white-space: nowrap; }
    table td { color: var(--text-muted); }
</style>

<div class="header-actions">
    <h1 data-tr="active_users">Active Users</h1>

    @php
        $totalMembers = $users->count();
        $onlineMembers = $users->filter(function($u) {
            return $u->last_seen && \Carbon\Carbon::parse($u->last_seen)->diffInMinutes(now()) < 5;
        })->count();
    @endphp

    {{-- SKELETON stat cards --}}
    <div id="skStats" style="display: flex; gap: 16px;">
        <div class="stat-card">
            <div class="skeleton sk-stat-icon"></div>
            <div><div class="skeleton sk-stat-label"></div><div class="skeleton sk-stat-val"></div></div>
        </div>
        <div class="stat-card">
            <div class="skeleton sk-stat-icon"></div>
            <div><div class="skeleton sk-stat-label"></div><div class="skeleton sk-stat-val"></div></div>
        </div>
    </div>

    {{-- REAL stat cards --}}
    <div id="realStats" style="display: none; gap: 16px; display:none;">
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--bg-active); color: var(--primary);">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;" data-tr="total_members">Total Members</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">{{ $totalMembers }}</div>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.2); color: var(--success);">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <div style="font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase;" data-tr="currently_online">Currently Online</div>
                <div style="font-size: 1.5rem; font-weight: 700; color: var(--text-main);">{{ $onlineMembers }}</div>
            </div>
        </div>
    </div>
</div>

{{-- === USER STATS + GROWTH CHART === --}}
<div style="display:grid;grid-template-columns:1fr 280px;gap:20px;margin-bottom:24px;">
    {{-- Growth Chart --}}
    <div class="card" style="margin:0;padding:24px;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;flex-wrap:wrap;gap:8px;">
            <div style="font-weight:700;font-size:.95rem;">👥 User Growth</div>
            <div style="display:flex;gap:8px;">
                <select id="userGrowthPeriod" class="form-control" style="width:auto;padding:4px 8px;font-size:0.85rem;height:32px;" onchange="loadUserGrowth()">
                    <option value="last30">Last 30 Days</option>
                    <option value="year">This Year</option>
                </select>
            </div>
        </div>
        <div style="position:relative;height:200px;">
            <canvas id="userGrowthChart"></canvas>
        </div>
    </div>
    {{-- Stats Cards --}}
    <div style="display:flex;flex-direction:column;gap:12px;">
        <div class="card" style="margin:0;padding:16px 20px;display:flex;align-items:center;gap:14px;">
            <div style="width:42px;height:42px;border-radius:12px;background:rgba(99,102,241,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Total Users</div>
                <div id="statTotalUsers" style="font-size:1.4rem;font-weight:800;color:#6366f1;margin-top:2px;">—</div>
            </div>
        </div>
        <div class="card" style="margin:0;padding:16px 20px;display:flex;align-items:center;gap:14px;">
            <div style="width:42px;height:42px;border-radius:12px;background:rgba(34,197,94,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" fill="none" stroke="#22c55e" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;">New This Month</div>
                <div id="statNewMonth" style="font-size:1.4rem;font-weight:800;color:#22c55e;margin-top:2px;">—</div>
            </div>
        </div>
        <div class="card" style="margin:0;padding:16px 20px;display:flex;align-items:center;gap:14px;">
            <div style="width:42px;height:42px;border-radius:12px;background:rgba(0,198,255,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <svg width="20" height="20" fill="none" stroke="#00c6ff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;">New Today</div>
                <div id="statNewToday" style="font-size:1.4rem;font-weight:800;color:#00c6ff;margin-top:2px;">—</div>
            </div>
        </div>
    </div>
</div>

<div style="margin-bottom: 20px; display: flex; justify-content: flex-end;">
    <div style="position: relative; width: 340px;">
        <div style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); pointer-events: none;">
            <svg id="searchIcon" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
            <svg id="loadingIcon" style="display:none; animation: spin 0.8s linear infinite;" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
        </div>
        <input
            type="text"
            id="liveSearchInput"
            class="form-control"
            placeholder="Search name, email, or Discord ID..."
            value="{{ request('search') }}"
            style="width: 100%; padding-left: 42px; padding-right: 40px;"
            autocomplete="off"
        >
        <button id="clearSearchBtn" onclick="clearSearch()" style="display:none; position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; font-size: 1.1rem; line-height: 1;">&#x2715;</button>
    </div>
    @if(request('search'))
        <a href="{{ route('admin.users') }}" class="btn btn-secondary" style="margin-left: 8px;">Clear</a>
    @endif
</div>

<div id="searchResultCount" style="display:none; margin: -12px 0 14px; font-size: 0.82rem; color: var(--text-muted); text-align: right;"></div>

<div class="card" style="overflow-x: auto;">
    <table style="min-width: 1500px;">
        <thead>
            <tr>
                <th data-tr="user_discord_info">User & Discord Info</th>
                <th data-tr="role">Role</th>
                <th data-tr="status">Status</th>
                <th data-tr="network_isp">Network & ISP</th>
                <th data-tr="device_info">Device Info</th>
                <th data-tr="location">Location</th>
                <th data-tr="action">Action</th>
            </tr>
        </thead>
        <tbody id="skUserRows">
            @for($s = 0; $s < 5; $s++)
            <tr class="sk-tr">
                <td>
                    <div style="display:flex; align-items:center; gap:12px;">
                        <div class="skeleton sk-avatar"></div>
                        <div style="flex:1;"><div class="skeleton sk-cell w80" style="margin-bottom:6px;"></div><div class="skeleton sk-cell w50"></div></div>
                    </div>
                </td>
                <td><div class="skeleton sk-cell w30"></div></td>
                <td><div class="skeleton sk-cell w50"></div></td>
                <td><div class="skeleton sk-cell w80"></div></td>
                <td><div class="skeleton sk-cell w50"></div></td>
                <td><div class="skeleton sk-cell w50"></div></td>
                <td><div class="skeleton sk-cell w50"></div></td>
            </tr>
            @endfor
        </tbody>
        <tbody id="realUserRows" style="display:none;">
            @foreach($users as $u)
                @php
                    $isOnline = $u->last_seen && \Carbon\Carbon::parse($u->last_seen)->diffInMinutes(now()) < 5;
                @endphp
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <img src="{{ $u->avatar ?? 'https://ui-avatars.com/api/?name='.$u->name.'&background=random' }}" alt="Avatar" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover; border: 1px solid var(--border-color);">
                            <div>
                                <a href="{{ route('profile.show', ['name' => strtolower($u->name), 'id' => $u->id]) }}" style="color: var(--primary); font-weight: 700; text-decoration: none;">
                                    {{ strtoupper($u->name) }}
                                </a>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">{{ $u->email }}</div>

                                <div style="font-size: 0.75rem; color: #5865F2; font-family: monospace; margin-top: 4px; font-weight: 600;">
                                    Discord ID: {{ $u->discord_id ?? 'No data' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td>
                        <span class="badge {{ $u->role == 'admin' ? 'badge-warning' : 'badge-success' }}">
                            {{ strtoupper($u->role) }}
                        </span>
                    </td>

                    <td>
                        <span class="status-dot {{ $isOnline ? 'online' : 'offline' }}"></span>
                        <span style="font-weight: 500;">{{ $isOnline ? 'Online' : 'Offline' }}</span>
                        <div style="font-size: 0.7rem; color: var(--text-muted); margin-top: 4px;">
                            {{ $u->last_seen ? \Carbon\Carbon::parse($u->last_seen)->diffForHumans() : 'Never' }}
                        </div>
                    </td>

                    <td>
                        <div style="font-family: monospace; color: var(--text-main); font-size: 0.875rem; font-weight: 600;">
                            {{ $u->last_ip ?? 'Unknown' }}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 4px;">
                            {{ $u->isp ?? 'Unknown ISP' }}
                        </div>
                    </td>

                    <td>
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="font-size: 1.25rem;" title="{{ $u->device_type }}">
                                {{ str_contains($u->device_type ?? '', 'Mobile') ? '📱' : '💻' }}
                            </div>
                            <div>
                                <div style="color: var(--text-main); font-weight: 600; font-size: 0.85rem;">
                                    {{ $u->os ?? 'Unknown OS' }}
                                </div>
                                <div style="font-size: 0.75rem; color: var(--text-muted); margin-top: 2px;">
                                    {{ $u->browser ?? 'Unknown Browser' }}
                                </div>
                            </div>
                        </div>
                    </td>

                    <td>
                        @if($u->location_flag)
                            <img src="{{ $u->location_flag }}" alt="Flag" style="width: 20px; border-radius: 2px; vertical-align: text-bottom; margin-right: 6px;">
                        @endif
                        <span style="font-size: 0.875rem;">{{ $u->location_city ?? 'Unknown' }}, {{ $u->location_country ?? 'Unknown' }}</span>
                    </td>

                    <td>
                        <div style="display: flex; gap: 8px;">
                            <button type="button" class="btn btn-sm btn-info" onclick="openHistoryModal({{ $u->id }}, '{{ $u->name }}')" style="background-color: #0ea5e9; color: white;">
                                <span>Info</span>
                            </button>

                            <button type="button" class="btn btn-sm btn-primary" onclick="openEditModal({{ $u->id }}, '{{ $u->email }}', '{{ $u->name }}', '{{ $u->role }}')">
                                <span data-tr="edit">Edit</span>
                            </button>

                            <form action="{{ route('admin.user.delete', $u->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                @csrf
                                @method('POST')
                                <button type="submit" class="btn btn-sm btn-danger"><span data-tr="delete">Delete</span></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

@if($users instanceof \Illuminate\Pagination\LengthAwarePaginator && $users->hasPages())
    <div style="padding: 16px 0; display:flex; justify-content:flex-end;">
        {{ $users->links() }}
    </div>
@endif

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const skS   = document.getElementById('skStats');
        const realS = document.getElementById('realStats');
        const skR   = document.getElementById('skUserRows');
        const realR = document.getElementById('realUserRows');

        setTimeout(() => {
            [skS, skR].forEach(el => {
                el.style.transition = 'opacity 0.3s ease';
                el.style.opacity = '0';
            });
            setTimeout(() => {
                skS.style.display = 'none';
                skR.style.display = 'none';
                realS.style.display = 'flex';
                realR.style.display = '';
                [realS, realR].forEach(el => {
                    el.style.opacity = '0';
                    el.style.transition = 'opacity 0.4s ease';
                });
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    realS.style.opacity = '1';
                    realR.style.opacity = '1';
                }));
            }, 300);
        }, 500);
    });

    // === LIVE SEARCH ===
    const searchInput  = document.getElementById('liveSearchInput');
    const clearBtn     = document.getElementById('clearSearchBtn');
    const searchIcon   = document.getElementById('searchIcon');
    const loadingIcon  = document.getElementById('loadingIcon');
    const resultCount  = document.getElementById('searchResultCount');
    const realRows     = document.getElementById('realUserRows');
    let searchTimer    = null;
    let originalRows   = null; // cache original server-rendered rows

    function clearSearch() {
        searchInput.value = '';
        clearBtn.style.display = 'none';
        resultCount.style.display = 'none';
        if (originalRows !== null) {
            realRows.innerHTML = originalRows;
        }
    }

    function setLoading(on) {
        searchIcon.style.display  = on ? 'none' : 'block';
        loadingIcon.style.display = on ? 'block' : 'none';
    }

    function roleBadge(role) {
        const cls = role === 'admin' ? 'badge-warning' : 'badge-success';
        return `<span class="badge ${cls}">${role.toUpperCase()}</span>`;
    }

    function buildRow(u) {
        const flagImg = u.location_flag ? `<img src="${u.location_flag}" alt="Flag" style="width:20px;border-radius:2px;vertical-align:text-bottom;margin-right:6px;">` : '';
        const deviceEmoji = (u.device_type||'').includes('Mobile') ? '📱' : '💻';
        const statusDot = u.is_online
            ? `<span class="status-dot online"></span><span style="font-weight:500;">Online</span>`
            : `<span class="status-dot offline"></span><span style="font-weight:500;">Offline</span>`;
        const lastSeenDiv = `<div style="font-size:0.7rem;color:var(--text-muted);margin-top:4px;">${u.last_seen}</div>`;

        return `<tr>
            <td>
                <div style="display:flex;align-items:center;gap:12px;">
                    <img src="${u.avatar}" alt="Avatar" style="width:40px;height:40px;border-radius:50%;object-fit:cover;border:1px solid var(--border-color);">
                    <div>
                        <a href="${u.profile_url}" style="color:var(--primary);font-weight:700;text-decoration:none;">${u.name.toUpperCase()}</a>
                        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">${u.email}</div>
                        <div style="font-size:0.75rem;color:#5865F2;font-family:monospace;margin-top:4px;font-weight:600;">Discord ID: ${u.discord_id}</div>
                    </div>
                </div>
            </td>
            <td>${roleBadge(u.role)}</td>
            <td>${statusDot}${lastSeenDiv}</td>
            <td>
                <div style="font-family:monospace;color:var(--text-main);font-size:0.875rem;font-weight:600;">${u.last_ip}</div>
                <div style="font-size:0.75rem;color:var(--text-muted);margin-top:4px;">${u.isp}</div>
            </td>
            <td>
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="font-size:1.25rem;">${deviceEmoji}</div>
                    <div>
                        <div style="color:var(--text-main);font-weight:600;font-size:0.85rem;">${u.os}</div>
                        <div style="font-size:0.75rem;color:var(--text-muted);margin-top:2px;">${u.browser}</div>
                    </div>
                </div>
            </td>
            <td>${flagImg}<span style="font-size:0.875rem;">${u.location_city}, ${u.location_country}</span></td>
            <td>
                <div style="display:flex;gap:8px;">
                    <a href="${u.profile_url}" class="btn btn-sm" style="background:#0ea5e9;color:white;text-decoration:none;">View</a>
                </div>
            </td>
        </tr>`;
    }

    searchInput.addEventListener('input', function() {
        const q = this.value.trim();
        clearBtn.style.display = q ? 'block' : 'none';

        // Cache original HTML once
        if (originalRows === null) {
            originalRows = realRows.innerHTML;
        }

        if (!q) {
            clearSearch();
            return;
        }

        clearTimeout(searchTimer);
        searchTimer = setTimeout(async () => {
            setLoading(true);
            try {
                const res = await fetch(`{{ route('admin.users.search') }}?q=${encodeURIComponent(q)}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const users = await res.json();

                resultCount.style.display = 'block';
                resultCount.textContent = `${users.length} result${users.length !== 1 ? 's' : ''} found`;

                if (users.length === 0) {
                    realRows.innerHTML = `<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--text-muted);">No users found matching "${q}"</td></tr>`;
                } else {
                    realRows.innerHTML = users.map(buildRow).join('');
                }
            } catch(e) {
                console.error('Search error:', e);
            } finally {
                setLoading(false);
            }
        }, 280);
    });

    // Add CSS spin animation
    const style = document.createElement('style');
    style.textContent = '@keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }';
    document.head.appendChild(style);
</script>

<div id="editEmailModal" class="modal">
    <div class="modal-content">
        <h3 style="margin-top:0; margin-bottom: 8px; color:var(--text-main); font-weight: 600; text-align: center;" data-tr="edit_user">Edit User</h3>
        <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom: 24px; text-align: center;"><span data-tr="user">User</span>: <span id="editUserName" style="color:var(--primary); font-weight:600;"></span></p>

        <form id="editEmailForm" method="POST" action="">
            @csrf
            <div class="form-group">
                <label class="form-label" style="text-align: left;" data-tr="email_address">Email Address</label>
                <input type="email" name="email" id="editEmailInput" class="form-control" required placeholder="Enter new email...">
            </div>
            <div class="form-group">
                <label class="form-label" style="text-align: left;" data-tr="user_role">User Role</label>
                <select name="role" id="editRoleInput" class="form-control" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div style="display:flex; gap:12px; margin-top: 24px;">
                <button type="button" class="btn btn-secondary" style="flex:1; justify-content: center;" onclick="closeEditModal()"><span data-tr="cancel">Cancel</span></button>
                <button type="submit" class="btn btn-primary" style="flex:1; justify-content: center;"><span data-tr="save">Save</span></button>
            </div>
        </form>
    </div>
</div>

{{-- History Modal --}}
<div id="historyModal" class="modal">
    <div class="modal-content" style="width: 980px; max-width: 96vw; max-height: 90vh; display: flex; flex-direction: column;">
        <h3 style="margin-top:0; margin-bottom: 8px; color:var(--text-main); font-weight: 600;">Transaction History</h3>
        <p style="color:var(--text-muted); font-size:0.875rem; margin-bottom: 24px;"><span data-tr="user">User</span>: <span id="historyUserName" style="color:var(--primary); font-weight:600;"></span></p>

        <div style="overflow-y: auto; flex: 1;">
            <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.875rem;">
                <thead style="background: var(--bg-surface); color: var(--text-muted);">
                    <tr>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Date</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Invoice</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Product</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Serial / Key</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Payment Info</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Price</th>
                        <th style="padding: 10px; border-bottom: 1px solid var(--border-color);">Status</th>
                    </tr>
                </thead>
                <tbody id="historyTableBody">
                    <tr><td colspan="5" style="text-align: center; padding: 20px;">Loading...</td></tr>
                </tbody>
            </table>
        </div>

        <div style="display:flex; justify-content: flex-end; margin-top: 24px; border-top: 1px solid var(--border-color); padding-top: 16px;">
            <button type="button" class="btn btn-secondary" onclick="closeHistoryModal()">Close</button>
        </div>
    </div>
</div>

<script>
    function openEditModal(userId, currentEmail, userName, currentRole) {
        document.getElementById('editUserName').innerText = userName.toUpperCase();
        document.getElementById('editEmailInput').value = currentEmail;
        document.getElementById('editRoleInput').value = currentRole;

        let actionUrl = "{{ url('/admin/users/update') }}/" + userId;
        document.getElementById('editEmailForm').action = actionUrl;

        document.getElementById('editEmailModal').style.display = 'flex';
        document.body.classList.add('modal-open');
    }

    function closeEditModal() {
        document.getElementById('editEmailModal').style.display = 'none';
        document.body.classList.remove('modal-open');
    }

    function openHistoryModal(userId, userName) {
        document.getElementById('historyUserName').innerText = userName.toUpperCase();
        document.getElementById('historyModal').style.display = 'flex';
        document.body.classList.add('modal-open');
        document.getElementById('historyTableBody').innerHTML = '<tr><td colspan="7" style="text-align: center; padding: 20px;">Loading...</td></tr>';

        fetch(`{{ url('/admin/users') }}/${userId}/history`)
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('historyTableBody');
                if (data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" style="text-align: center; padding: 20px; color: var(--text-muted);">No transactions found.</td></tr>';
                    return;
                }

                tbody.innerHTML = '';
                data.forEach(trx => {
                    let statusBadge = '';
                    if(trx.status === 'PAID') statusBadge = '<span class="badge badge-success">PAID</span>';
                    else if(trx.status === 'UNPAID') statusBadge = '<span class="badge badge-warning">PENDING</span>';
                    else statusBadge = '<span class="badge badge-danger">FAILED</span>';

                    let priceFmt = trx.payment_method === 'STRIPE'
                        ? '$ ' + (trx.price / 100).toFixed(2)
                        : 'Rp ' + parseInt(trx.price).toLocaleString('id-ID');

                    let dateObj = new Date(trx.created_at);
                    let dateStr = dateObj.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });

                    let payInfoStr = `<div style="font-weight: 600; font-size: 0.8rem; color: var(--text-main);">${trx.payment_method || '-'}</div>`;
                    if (trx.payment_details) {
                        try {
                            let details = typeof trx.payment_details === 'string' ? JSON.parse(trx.payment_details) : trx.payment_details;
                            let subInfo = '';
                            if (details.brand && details.last4) subInfo = `${details.brand} **** ${details.last4}`;
                            else if (details.issuer) subInfo = `QRIS (${details.issuer.toUpperCase()})`;
                            else if (details.bank) subInfo = `Bank: ${details.bank.toUpperCase()}`;
                            else if (details.type) subInfo = details.type.toUpperCase();

                            if (subInfo) {
                                payInfoStr += `<div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">${subInfo}</div>`;
                            }
                        } catch (e) {}
                    }

                    let serialStr = trx.vouchers_issued
                        ? `<span style="font-family:monospace; font-size:0.78rem; color:#22c55e; background:rgba(34,197,94,0.08); padding:3px 8px; border-radius:5px; display:inline-block; max-width:180px; word-break:break-all;">${trx.vouchers_issued}</span>`
                        : `<span style="color:var(--text-muted); font-size:0.75rem;">—</span>`;

                    let row = `
                        <tr>
                            <td style="padding: 12px 10px; border-bottom: 1px solid var(--border-color); color: var(--text-muted);">${dateStr}</td>
                            <td style="padding: 12px 10px; border-bottom: 1px solid var(--border-color); font-family: monospace; color: var(--primary);">#${trx.merchant_ref}</td>
                            <td style="padding: 12px 10px; border-bottom: 1px solid var(--border-color);">${trx.product_name}</td>
                            <td style="padding: 12px 10px; border-bottom: 1px solid var(--border-color);">${serialStr}</td>
                            <td style="padding: 12px 10px; border-bottom: 1px solid var(--border-color);">${payInfoStr}</td>
                            <td style="padding: 12px 10px; border-bottom: 1px solid var(--border-color); font-weight: 600;">${priceFmt}</td>
                            <td style="padding: 12px 10px; border-bottom: 1px solid var(--border-color);">${statusBadge}</td>
                        </tr>
                    `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            })
            .catch(err => {
                document.getElementById('historyTableBody').innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px; color: var(--danger);">Failed to load data.</td></tr>';
            });
    }

    function closeHistoryModal() {
        document.getElementById('historyModal').style.display = 'none';
        document.body.classList.remove('modal-open');
    }
</script>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let userGrowthChartInstance = null;

async function loadUserGrowth() {
    const period = document.getElementById('userGrowthPeriod').value;
    try {
        const res = await fetch(`{{ route('admin.users.growth') }}?period=${period}`);
        const data = await res.json();

        document.getElementById('statTotalUsers').textContent = data.total_users.toLocaleString();
        document.getElementById('statNewMonth').textContent = data.new_this_month.toLocaleString();
        document.getElementById('statNewToday').textContent = data.new_today.toLocaleString();

        const ctx = document.getElementById('userGrowthChart').getContext('2d');
        const isDark = localStorage.getItem('abuser_admin_theme') !== 'light';
        const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
        const labelColor = isDark ? '#8892a4' : '#6b7280';

        const grad = ctx.createLinearGradient(0, 0, 0, 200);
        grad.addColorStop(0, 'rgba(99,102,241,0.35)');
        grad.addColorStop(1, 'rgba(99,102,241,0.02)');

        if (userGrowthChartInstance) {
            userGrowthChartInstance.data.labels = data.labels;
            userGrowthChartInstance.data.datasets[0].data = data.data;
            userGrowthChartInstance.update();
        } else {
            userGrowthChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'New Users',
                        data: data.data,
                        borderColor: '#6366f1',
                        backgroundColor: grad,
                        borderWidth: 2.5,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                        tension: 0.4,
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: '#1b1e2b',
                            borderColor: '#2a2d3e',
                            borderWidth: 1,
                            titleColor: '#fff',
                            bodyColor: '#8892a4',
                            padding: 10,
                        }
                    },
                    scales: {
                        x: { grid: { color: gridColor }, ticks: { color: labelColor, maxTicksLimit: 8, font: { size: 11 } } },
                        y: {
                            grid: { color: gridColor },
                            ticks: { color: labelColor, font: { size: 10 }, stepSize: 1, precision: 0 },
                            beginAtZero: true,
                        }
                    }
                }
            });
        }
    } catch(e) { console.error('Failed to load user growth', e); }
}

document.addEventListener('DOMContentLoaded', loadUserGrowth);
</script>
@endsection
