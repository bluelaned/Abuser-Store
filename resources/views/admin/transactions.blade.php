@extends('admin.layouts.app')

@section('title', 'Transaction History')

@section('content')

<div class="header-actions">
    <h1 data-tr="transaction_history">Transaction History</h1>
    <div style="display: flex; align-items: center; gap: 15px;">
        <div style="color:var(--text-muted); font-size: 0.875rem; font-weight: 500;">
            Total: {{ $transactions->total() }} <span data-tr="transactions">Transactions</span>
        </div>
        @if($transactions->total() > 0)
            <form action="{{ route('admin.transactions.truncate') }}" method="POST" onsubmit="return confirm('WARNING: Delete all transaction history? This action cannot be undone!');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span data-tr="delete_all_history">Delete All History</span>
                </button>
            </form>
        @endif
    </div>
</div>

{{-- ── Summary Stats ── --}}
<div class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    {{-- IDR Revenue --}}
    <div class="card" style="padding: 20px 22px; display: flex; align-items: center; gap: 14px; margin:0;">
        <div style="width:42px;height:42px;border-radius:12px;background:rgba(99,102,241,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="20" height="20" fill="none" stroke="#6366f1" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div style="min-width:0;">
            <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;">Total Revenue</div>
            @if($idrRevenue > 0)
                <div style="font-size:1.1rem;font-weight:800;color:var(--text-main);margin-top:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">Rp {{ number_format($idrRevenue,0,',','.') }}</div>
            @endif
            @if($usdRevenue > 0)
                <div style="font-size:{{ $idrRevenue > 0 ? '.85rem' : '1.1rem' }};font-weight:800;color:#6366f1;margin-top:2px;">$ {{ number_format($usdRevenue,2) }} USD</div>
            @endif
            @if($idrRevenue == 0 && $usdRevenue == 0)
                <div style="font-size:1.1rem;font-weight:800;color:var(--text-muted);margin-top:2px;">Rp 0</div>
            @endif
        </div>
    </div>
    {{-- Paid Orders --}}
    <div class="card" style="padding: 20px 22px; display: flex; align-items: center; gap: 14px; margin:0;">
        <div style="width:42px;height:42px;border-radius:12px;background:rgba(34,197,94,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="20" height="20" fill="none" stroke="#22c55e" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;" data-tr="paid_orders">Paid Orders</div>
            <div style="font-size:1.25rem;font-weight:800;color:#22c55e;margin-top:2px;">{{ $totalPaid }}</div>
        </div>
    </div>
    {{-- Avg Order --}}
    <div class="card" style="padding: 20px 22px; display: flex; align-items: center; gap: 14px; margin:0;">
        <div style="width:42px;height:42px;border-radius:12px;background:rgba(0,198,255,.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="20" height="20" fill="none" stroke="#00c6ff" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        </div>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;" data-tr="avg_order">Avg. Order</div>
            @if($avgIDR > 0)
            <div style="font-size:1rem;font-weight:800;color:#00c6ff;margin-top:2px;">Rp {{ number_format($avgIDR,0,',','.') }}</div>
            @endif
            @if($avgUSD > 0)
            <div style="font-size:{{ $avgIDR > 0 ? '.85rem' : '1rem' }};font-weight:{{ $avgIDR > 0 ? '600' : '800' }};color:{{ $avgIDR > 0 ? '#8892a4' : '#00c6ff' }};margin-top:{{ $avgIDR > 0 ? '1px' : '2px' }};">$ {{ number_format($avgUSD,2) }}</div>
            @endif
            @if($avgIDR == 0 && $avgUSD == 0)
            <div style="font-size:1rem;font-weight:800;color:var(--text-muted);margin-top:2px;">—</div>
            @endif
        </div>
    </div>
    {{-- Pending / Failed --}}
    <div class="card" style="padding: 20px 22px; display: flex; align-items: center; gap: 14px; margin:0;">
        <div style="width:42px;height:42px;border-radius:12px;background:rgba(234,179,8,.15);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <svg width="20" height="20" fill="none" stroke="#eab308" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <div style="font-size:.72rem;color:var(--text-muted);font-weight:700;text-transform:uppercase;letter-spacing:.5px;" data-tr="pending_orders">Pending / Failed</div>
            <div style="font-size:1.25rem;font-weight:800;color:#eab308;margin-top:2px;">
                {{ $totalPending }} <span style="font-size:.9rem;color:var(--text-muted);">/ {{ $totalFailed }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ── Two-column: Chart + Top Products ── --}}
<div class="grid" style="display:grid; grid-template-columns: 1fr 340px; gap: 20px; margin-bottom: 24px; align-items: start;">

    {{-- Revenue Chart --}}
    <div class="card" style="padding: 24px; margin:0;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; flex-wrap: wrap; gap: 10px;">
            <div>
                <div style="font-weight:700; font-size:.95rem;" data-tr="revenue_chart" id="chartTitle">Revenue Chart</div>
                <div style="font-size:.78rem; color:var(--text-muted); margin-top:3px;">
                    <span style="display:inline-block;width:8px;height:8px;background:#6366f1;border-radius:50%;margin-right:4px;"></span>IDR (Midtrans)
                    &nbsp;
                    <span style="display:inline-block;width:8px;height:8px;background:#00c6ff;border-radius:50%;margin-right:4px;"></span>USD (Stripe)
                </div>
            </div>
            
            {{-- Filter UI --}}
            <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
                <select id="chartPeriod" class="form-control" style="width: auto; padding: 4px 8px; font-size: 0.85rem; height: 32px;" onchange="handlePeriodChange()">
                    <option value="last30">Last 30 Days</option>
                    <option value="year">1 Year</option>
                    <option value="custom">Custom Month/Year</option>
                </select>

                <select id="chartYear" class="form-control" style="width: auto; padding: 4px 8px; font-size: 0.85rem; height: 32px; display: none;" onchange="updateChart()">
                    @for($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>

                <select id="chartMonth" class="form-control" style="width: auto; padding: 4px 8px; font-size: 0.85rem; height: 32px; display: none;" onchange="updateChart()">
                    @foreach(['01'=>'Jan', '02'=>'Feb', '03'=>'Mar', '04'=>'Apr', '05'=>'May', '06'=>'Jun', '07'=>'Jul', '08'=>'Aug', '09'=>'Sep', '10'=>'Oct', '11'=>'Nov', '12'=>'Dec'] as $m => $name)
                        <option value="{{ $m }}" {{ now()->format('m') == $m ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- Fixed-height wrapper prevents infinite stretch --}}
        <div style="position:relative; height:220px;">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    {{-- Top Selling Products --}}
    <div class="card" style="padding: 24px; margin:0;">
        <div style="font-weight:700; font-size:.95rem; margin-bottom:16px;">🏆 <span data-tr="top_products">Top Selling Products</span></div>
        @if($topProducts->isEmpty())
            <div style="color:var(--text-muted); font-size:.875rem; text-align:center; padding:30px 0;">No data yet</div>
        @else
            @php $maxOrders = $topProducts->max('total_orders'); @endphp
            @foreach($topProducts as $i => $p)
            <div style="margin-bottom:14px;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:5px;">
                    <div style="font-size:.82rem; font-weight:600; color:var(--text-main); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:190px;" title="{{ $p->product_name }}">
                        <span style="color:var(--text-muted); margin-right:6px;">#{{ $i+1 }}</span>{{ $p->product_name }}
                    </div>
                    <div style="font-size:.75rem; background:var(--bg-surface); padding:2px 8px; border-radius:99px; font-weight:600; white-space:nowrap; margin-left:6px; color:var(--text-muted);">{{ $p->total_orders }} sold</div>
                </div>
                <div style="height:6px; background:var(--border-color); border-radius:99px; overflow:hidden;">
                    <div style="height:100%; width:{{ $maxOrders > 0 ? round(($p->total_orders/$maxOrders)*100) : 0 }}%; background: linear-gradient(90deg, #6366f1, #00c6ff); border-radius:99px;"></div>
                </div>
                <div style="font-size:.72rem; color:var(--text-muted); margin-top:3px;">
                    @if(strtoupper($p->payment_method) === 'STRIPE')
                        $ {{ number_format($p->total_revenue / 100, 2) }} USD revenue
                    @else
                        Rp {{ number_format($p->total_revenue,0,',','.') }} revenue
                    @endif
                </div>
            </div>
            @endforeach
        @endif
    </div>
</div>

{{-- ── Filter Bar ── --}}
<div class="card" style="padding: 18px 20px; margin-bottom: 0; border-bottom-left-radius: 0; border-bottom-right-radius: 0; border-bottom: none;">
    <form method="GET" action="{{ route('admin.transactions.index') }}" id="filterForm" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-end;">
        {{-- Search --}}
        <div style="flex: 1; min-width: 200px;">
            <label style="font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:6px;">Search</label>
            <div style="position:relative;">
                <svg style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:var(--text-muted);" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Invoice, product, email..." class="form-control" style="padding-left: 32px;">
            </div>
        </div>
        {{-- Status --}}
        <div style="min-width: 130px;">
            <label style="font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:6px;">Status</label>
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>Paid</option>
                <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pending</option>
                <option value="FAILED" {{ request('status') === 'FAILED' ? 'selected' : '' }}>Failed</option>
            </select>
        </div>
        {{-- Payment --}}
        <div style="min-width: 130px;">
            <label style="font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:6px;">Payment</label>
            <select name="payment" class="form-control">
                <option value="">All Methods</option>
                <option value="STRIPE" {{ request('payment') === 'STRIPE' ? 'selected' : '' }}>Stripe</option>
                <option value="QRIS" {{ request('payment') === 'QRIS' ? 'selected' : '' }}>QRIS</option>
                <option value="MIDTRANS" {{ request('payment') === 'MIDTRANS' ? 'selected' : '' }}>Midtrans</option>
            </select>
        </div>
        {{-- Date From --}}
        <div style="min-width: 140px;">
            <label style="font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:6px;">Date From</label>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control">
        </div>
        {{-- Date To --}}
        <div style="min-width: 140px;">
            <label style="font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:6px;">Date To</label>
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control">
        </div>
        {{-- Sort --}}
        <div style="min-width: 160px;">
            <label style="font-size:0.72rem; color:var(--text-muted); font-weight:700; text-transform:uppercase; letter-spacing:.5px; display:block; margin-bottom:6px;">Sort By</label>
            <select name="sort" class="form-control">
                <option value="date_desc" {{ request('sort', 'date_desc') === 'date_desc' ? 'selected' : '' }}>Date: Newest First</option>
                <option value="date_asc" {{ request('sort') === 'date_asc' ? 'selected' : '' }}>Date: Oldest First</option>
                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High → Low</option>
                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low → High</option>
            </select>
        </div>
        {{-- Buttons --}}
        <div style="display:flex; gap:8px; align-items:flex-end; padding-bottom: 1px;">
            <button type="submit" class="btn btn-primary" style="white-space:nowrap;">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="m21 21-4.35-4.35"/></svg>
                Filter
            </button>
            @if(request()->hasAny(['search','status','payment','date_from','date_to','sort']))
                <a href="{{ route('admin.transactions.index') }}" class="btn btn-secondary" style="white-space:nowrap;">Clear</a>
            @endif
        </div>
        @if(request()->hasAny(['search','status','payment','date_from','date_to']))
            <div style="width:100%; font-size:0.82rem; color:var(--text-muted); margin-top:-4px;">
                Showing <strong style="color:var(--text-main);">{{ $transactions->total() }}</strong> results
            </div>
        @endif
    </form>
</div>

{{-- ── Transaction Table ── --}}
<div class="card" style="border-top-left-radius: 0; border-top-right-radius: 0;">
    <table>
        <thead>
            <tr>
                <th data-tr="date">Date</th>
                <th>Invoice</th>
                <th data-tr="buyer">Buyer</th>
                <th>Payment Info</th>
                <th>Item</th>
                <th data-tr="price">Price</th>
                <th data-tr="status">Status</th>
                <th data-tr="action">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transactions as $trx)
            <tr>
                <td style="color:var(--text-muted); font-size:0.875rem;">{{ $trx->created_at->format('d M Y H:i') }}</td>
                <td style="font-family:monospace; color:var(--primary); font-weight: 500;">#{{ $trx->merchant_ref }}</td>
                <td>
                    <div style="font-weight: 600; color: var(--text-main);">{{ $trx->user->name ?? 'Guest' }}</div>
                    <div style="font-size: 0.75rem; color: var(--text-muted);">{{ $trx->customer_email }}</div>
                </td>
                <td>
                    <div style="font-weight: 600; font-size: 0.8rem; color: var(--text-main);">{{ $trx->payment_method }}</div>
                    @php $details = json_decode($trx->payment_details, true); @endphp
                    @if($details)
                        <div style="font-size: 0.72rem; color: var(--text-muted); margin-top: 2px;">
                            @if(isset($details['brand']) && isset($details['last4']))
                                {{ $details['brand'] }} **** {{ $details['last4'] }}
                            @elseif(isset($details['issuer']))
                                QRIS ({{ strtoupper($details['issuer']) }})
                            @elseif(isset($details['bank']))
                                Bank: {{ strtoupper($details['bank']) }}
                            @elseif(isset($details['type']))
                                {{ strtoupper($details['type']) }}
                            @endif
                        </div>
                    @endif
                </td>
                <td>
                    <div style="font-weight: 500;">{{ $trx->product_name }}</div>
                    @if($trx->vouchers_issued)
                        <div style="font-size: 0.75rem; color: var(--success); font-family: monospace; margin-top: 4px;">
                            {{ $trx->vouchers_issued }}
                        </div>
                    @endif
                </td>
                <td style="font-weight:600;">
                    @if(strtoupper($trx->payment_method) === 'STRIPE')
                        $ {{ number_format($trx->price / 100, 2) }}
                    @else
                        Rp {{ number_format($trx->price, 0, ',', '.') }}
                    @endif
                </td>
                <td>
                    @if($trx->status == 'PAID')
                        <span class="badge badge-success" data-tr="paid">PAID</span>
                    @elseif($trx->status == 'UNPAID')
                        <span class="badge badge-warning" data-tr="pending">PENDING</span>
                    @else
                        <span class="badge badge-danger" data-tr="failed">FAILED</span>
                    @endif
                </td>
                <td>
                    <form action="{{ route('admin.transaction.destroy', $trx->id) }}" method="POST" onsubmit="return confirm('Delete?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"><span data-tr="delete">Delete</span></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center; padding:40px; color:var(--text-muted);" data-tr="no_transactions">No transaction data yet.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if($transactions->hasPages())
    <div style="padding: 16px 0 4px; display:flex; justify-content:flex-end;">
        {{ $transactions->links() }}
    </div>
    @endif
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let revenueChartInstance = null;

function handlePeriodChange() {
    const period = document.getElementById('chartPeriod').value;
    const yearSelect = document.getElementById('chartYear');
    const monthSelect = document.getElementById('chartMonth');
    const chartTitle = document.getElementById('chartTitle');

    if (period === 'last30') {
        yearSelect.style.display = 'none';
        monthSelect.style.display = 'none';
        chartTitle.innerText = 'Revenue - Last 30 Days';
    } else if (period === 'year') {
        yearSelect.style.display = 'block';
        monthSelect.style.display = 'none';
        chartTitle.innerText = 'Revenue - Yearly Overview';
    } else if (period === 'custom') {
        yearSelect.style.display = 'block';
        monthSelect.style.display = 'block';
        chartTitle.innerText = 'Revenue - Custom Month';
    }
    
    updateChart();
}

async function updateChart() {
    const period = document.getElementById('chartPeriod').value;
    const year = document.getElementById('chartYear').value;
    const month = document.getElementById('chartMonth').value;

    try {
        const response = await fetch(`{{ route('admin.transactions.chart') }}?period=${period}&year=${year}&month=${month}`);
        const data = await response.json();

        if (revenueChartInstance) {
            revenueChartInstance.data.labels = data.labels;
            revenueChartInstance.data.datasets[0].data = data.dataIDR;
            revenueChartInstance.data.datasets[1].data = data.dataUSD;
            revenueChartInstance.update();
        } else {
            initChart(data.labels, data.dataIDR, data.dataUSD);
        }
    } catch (error) {
        console.error("Error fetching chart data", error);
    }
}

function initChart(labels, dataIDR, dataUSD) {
    const ctx = document.getElementById('revenueChart').getContext('2d');
    const isDark = localStorage.getItem('abuser_admin_theme') !== 'light';
    const gridColor  = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const labelColor = isDark ? '#8892a4' : '#6b7280';

    const gradIDR = ctx.createLinearGradient(0, 0, 0, 220);
    gradIDR.addColorStop(0, 'rgba(99,102,241,0.35)');
    gradIDR.addColorStop(1, 'rgba(99,102,241,0.02)');

    const gradUSD = ctx.createLinearGradient(0, 0, 0, 220);
    gradUSD.addColorStop(0, 'rgba(0,198,255,0.25)');
    gradUSD.addColorStop(1, 'rgba(0,198,255,0.02)');

    revenueChartInstance = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'IDR (Midtrans)',
                    data: dataIDR,
                    borderColor: '#6366f1',
                    backgroundColor: gradIDR,
                    borderWidth: 2.5,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#6366f1',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'yIDR',
                },
                {
                    label: 'USD (Stripe)',
                    data: dataUSD,
                    borderColor: '#00c6ff',
                    backgroundColor: gradUSD,
                    borderWidth: 2,
                    pointRadius: 3,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#00c6ff',
                    tension: 0.4,
                    fill: true,
                    yAxisID: 'yUSD',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1b1e2b',
                    borderColor: '#2a2d3e',
                    borderWidth: 1,
                    titleColor: '#fff',
                    bodyColor: '#8892a4',
                    padding: 10,
                    callbacks: {
                        label: ctx => {
                            if (ctx.dataset.yAxisID === 'yUSD')
                                return '  USD $ ' + ctx.parsed.y.toFixed(2);
                            return '  IDR Rp ' + ctx.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { color: gridColor },
                    ticks: { color: labelColor, maxTicksLimit: 8, font: { size: 11 } }
                },
                yIDR: {
                    position: 'left',
                    grid: { color: gridColor },
                    ticks: {
                        color: '#6366f1',
                        font: { size: 10 },
                        callback: v => {
                            if (v >= 1000000) return 'Rp' + (v/1000000).toFixed(1)+'M';
                            if (v >= 1000)    return 'Rp' + (v/1000).toFixed(0)+'K';
                            return 'Rp' + v;
                        }
                    }
                },
                yUSD: {
                    position: 'right',
                    grid: { drawOnChartArea: false },
                    ticks: {
                        color: '#00c6ff',
                        font: { size: 10 },
                        callback: v => '$' + v.toFixed(0)
                    }
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // Initialize chart with default selected period
    updateChart();
});
</script>
@endsection