@extends('admin.layouts.app')

@section('title', 'Manage Stock - ' . $product->name)

@section('content')
<style>
    /* Modal Style */
    .modal { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999; align-items: center; justify-content: center; backdrop-filter: blur(4px); }
    .modal-content { background: var(--bg-card); padding: 32px; border-radius: 12px; width: 500px; max-width: 90vw; border: 1px solid var(--border-color); max-height: 85vh; overflow-y: auto; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    
    textarea { width: 100%; background: var(--bg-body); color: var(--text-main); border: 1px solid var(--border-color); padding: 16px; border-radius: 8px; font-family: monospace; margin-top: 8px; resize: vertical; outline: none; transition: border-color 0.2s; }
    textarea:focus { border-color: var(--primary); }

    /* List Voucher */
    .code-list { list-style: none; padding: 0; margin-top: 16px; }
    .code-item { background: var(--bg-body); padding: 12px 16px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; font-family: monospace; font-size: 0.875rem; }
    .code-item:first-child { border-top: 1px solid var(--border-color); }
    .btn-del-icon { background: none; border: none; color: var(--danger); cursor: pointer; font-size: 1.1rem; opacity: 0.7; transition: opacity 0.2s; }
    .btn-del-icon:hover { opacity: 1; }
</style>

<div class="header-actions" style="margin-bottom: 16px;">
    <div>
        <h1 style="margin-bottom: 8px;" data-tr="manage_stock">Manage Stock</h1>
        <p style="color:var(--text-muted); font-size:0.875rem; margin: 0;"><span data-tr="product">Product</span>: <strong style="color:var(--text-main); font-weight: 600;">{{ $product->name }}</strong></p>
    </div>
    
    <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
        <span data-tr="back_to_dashboard">Back to Dashboard</span>
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success" style="background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; margin-bottom: 24px;">
        {{ session('success') }}
    </div>
@endif

<div class="card">
    <table>
        <thead>
            <tr>
                <th width="35%" data-tr="package_duration">Package Name (Duration)</th>
                <th width="35%" data-tr="stock_status">Stock Status</th>
                <th width="30%" data-tr="action">Action</th>
            </tr>
        </thead>
        <tbody>
            @foreach($product->variants as $v)
                @php
                    $available = $v->vouchers->where('status', 'AVAILABLE')->count();
                    $total = $v->vouchers->count();
                @endphp
                <tr>
                    <td>
                        <div style="font-weight: 600; color: var(--text-main); font-size: 1rem;">{{ $v->duration }}</div>
                        <div style="font-size: 0.75rem; color: var(--text-muted); font-family: monospace; margin-top: 4px;">ID: #{{ $v->id }}</div>
                    </td>
                    <td>
                        @if($available > 0)
                            <span class="badge badge-success">{{ $available }} <span data-tr="pcs_ready">pcs Ready</span></span>
                        @else
                            <span class="badge badge-danger" data-tr="out_of_stock">Out of Stock</span>
                        @endif
                    </td>
                    <td>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn btn-sm btn-primary" onclick="openModal('add-{{ $v->id }}')"><span data-tr="add_stock">+ Add Stock</span></button>
                            <button class="btn btn-sm btn-secondary" onclick="openModal('list-{{ $v->id }}')"><span data-tr="view_codes">View Codes</span></button>
                        </div>
                    </td>
                </tr>

                <div id="modal-add-{{ $v->id }}" class="modal">
                    <div class="modal-content">
                        <h3 style="margin-top:0; color:var(--text-main); font-size: 1.125rem;"><span data-tr="add_stock_for">Add Stock</span>: <span style="color:var(--primary);">{{ $v->duration }}</span></h3>
                        <form action="{{ route('admin.vouchers.store') }}" method="POST">
                            @csrf
                            <input type="hidden" name="variant_id" value="{{ $v->id }}">
                            <div class="form-group" style="margin-top: 16px;">
                                <label class="form-label" style="font-size: 0.875rem;" data-tr="enter_voucher_codes">Enter Voucher Codes (1 line = 1 code)</label>
                                <textarea name="code" rows="8" placeholder="Example:&#10;AAAA-BBBB-CCCC&#10;XXXX-YYYY-ZZZZ" required></textarea>
                            </div>
                            <div style="margin-top:24px; display:flex; gap:12px;">
                                <button type="button" class="btn btn-secondary" onclick="closeModal('add-{{ $v->id }}')" style="flex:1; justify-content: center;"><span data-tr="cancel">Cancel</span></button>
                                <button type="submit" class="btn btn-primary" style="flex:1; justify-content: center;"><span data-tr="save">Save</span></button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Modal Lihat Stok -->
                <div id="modal-list-{{ $v->id }}" class="modal">
                    <div class="modal-content">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <h3 style="margin:0; color:var(--text-main); font-size: 1.125rem;"><span data-tr="code_data">Code Data</span>: {{ $v->duration }}</h3>
                            <button onclick="closeModal('list-{{ $v->id }}')" style="background:none; border:none; color:var(--text-muted); font-size:1.5rem; cursor:pointer;">&times;</button>
                        </div>

                        @if($v->vouchers->where('status', 'AVAILABLE')->count() > 0)
                            <div style="max-height:300px; overflow-y:auto; margin-top:16px; border:1px solid var(--border-color); border-radius:8px;">
                                <ul class="code-list" style="margin:0;">
                                    @foreach($v->vouchers->where('status', 'AVAILABLE') as $voucher)
                                        <li class="code-item">
                                            <div style="display: flex; align-items: center; gap: 12px;">
                                                <span style="color: var(--success); font-weight:600;">
                                                    {{ $voucher->code }}
                                                </span>
                                            </div>
                                            <form action="{{ route('admin.vouchers.destroy', $voucher->id) }}" method="POST" style="margin: 0;" onsubmit="return confirm('Delete permanently?');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn-del-icon" title="Delete">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        @else
                            <p style="color:var(--text-muted); text-align:center; padding:40px 20px;" data-tr="no_voucher_data">No voucher data yet.</p>
                        @endif

                        <button type="button" onclick="closeModal('list-{{ $v->id }}')" class="btn btn-secondary" style="width:100%; margin-top:24px; justify-content: center;"><span data-tr="close">Close</span></button>
                    </div>
                </div>

            @endforeach
        </tbody>
    </table>
</div>

<script>
    function openModal(id) { document.getElementById('modal-' + id).style.display = 'flex'; }
    function closeModal(id) { document.getElementById('modal-' + id).style.display = 'none'; }
    
    // Klik luar modal tutup
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = 'none';
        }
    }
</script>
@endsection