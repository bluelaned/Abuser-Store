@extends('admin.layouts.app')

@section('title', 'Manage Promos')

@section('content')
<div class="header-actions">
    <div>
        <h1 data-tr="manage_promos_title" style="display:flex; align-items:center;">
            Manage Promo Codes
            <svg data-tr-title="manage_promos_info" title="Manage, create, and monitor discount codes for your store." width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="cursor:help; margin-left:10px; color:var(--text-muted);"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
        </h1>
        <p style="color:var(--text-muted); font-size:0.875rem; margin-top:8px;" data-tr="create_discount_codes">Create discount codes for customers.</p>
    </div>
</div>

<div class="card" style="background: var(--bg-body); border: 1px dashed var(--border-color);">
    <h3 style="margin-top:0; color:var(--text-main); margin-bottom: 16px; font-size:1.125rem;" data-tr="create_new_code">Create New Code</h3>
    <form action="{{ route('admin.promos.store') }}" method="POST" class="grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 16px; align-items: end;">
        @csrf
        
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" data-tr="promo_code" style="display:flex; align-items:center;">
                Promo Code
                <svg data-tr-title="promo_code_desc" title="Enter a unique code for this promo. (Example: SUMMER50)" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="cursor:help; margin-left:6px; color:var(--text-muted);"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            </label>
            <input type="text" name="code" class="form-control" placeholder="e.g: COOLADMIN" required>
            <small style="color:var(--text-muted); margin-top:4px; display:block; font-size: 0.75rem;" data-tr="promo_code_desc">Enter a unique code for this promo. (Example: SUMMER50)</small>
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" data-tr="discount_type" style="display:flex; align-items:center;">
                Discount Type
                <svg data-tr-title="discount_type_desc" title="Choose the discount type, either a fixed nominal cut or a percentage of the total price." width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="cursor:help; margin-left:6px; color:var(--text-muted);"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            </label>
            <select name="type" class="form-control">
                <option value="fixed" data-tr="fixed_amount">Fixed Amount (Rp)</option>
                <option value="percent" data-tr="percentage_discount">Percentage (%)</option>
            </select>
            <small style="color:var(--text-muted); margin-top:4px; display:block; font-size: 0.75rem;" data-tr="discount_type_desc">Choose between a fixed nominal cut or a percentage discount.</small>
        </div>
        
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" data-tr="discount_value" style="display:flex; align-items:center;">
                Discount Value
                <svg data-tr-title="discount_value_desc" title="The amount of discount to be given. (e.g., 5000 or 10)" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="cursor:help; margin-left:6px; color:var(--text-muted);"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            </label>
            <input type="number" name="value" class="form-control" placeholder="e.g: 5000" required>
            <small style="color:var(--text-muted); margin-top:4px; display:block; font-size: 0.75rem;" data-tr="discount_value_desc">The amount of discount to be given. (e.g., 5000 or 10)</small>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="display:flex; align-items:center;">
                Max Discount (Rp)
                <svg data-tr-title="max_discount_desc" title="Maximum discount limit specifically for Percentage (%) discounts. Enter 0 for no limit." width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="cursor:help; margin-left:6px; color:var(--text-muted);"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            </label>
            <input type="number" name="max_discount" class="form-control" placeholder="0 = Unlimited. Only for % type" value="0" min="0">
            <small style="color:var(--text-muted); margin-top:4px; display:block; font-size: 0.75rem;" data-tr="max_discount_desc">Maximum discount limit for Percentage (%) discounts. 0 = Unlimited.</small>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="display:flex; align-items:center;">
                Usage Limit Per User
                <svg data-tr-title="usage_limit_desc" title="Maximum number of times a user can use this promo code. Enter 0 for unlimited use." width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="cursor:help; margin-left:6px; color:var(--text-muted);"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            </label>
            <input type="number" name="usage_limit_per_user" class="form-control" placeholder="0 = Unlimited" value="1" min="0">
            <small style="color:var(--text-muted); margin-top:4px; display:block; font-size: 0.75rem;" data-tr="usage_limit_desc">Maximum use per user. 0 = Unlimited.</small>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="display:flex; align-items:center;">
                Min. Qty Required
                <svg data-tr-title="min_qty_desc" title="Minimum number of items that must be purchased in a single transaction for this promo to be valid." width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="cursor:help; margin-left:6px; color:var(--text-muted);"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            </label>
            <input type="number" name="min_qty" class="form-control" placeholder="Minimum quantity" value="1" min="1">
            <small style="color:var(--text-muted); margin-top:4px; display:block; font-size: 0.75rem;" data-tr="min_qty_desc">Minimum items required in one transaction.</small>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label" style="display:flex; align-items:center;">
                Specific Product
                <svg data-tr-title="specific_product_desc" title="Determine if this promo is only valid for specific products or all products." width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="cursor:help; margin-left:6px; color:var(--text-muted);"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            </label>
            <select name="product_id" class="form-control">
                <option value="">All Products</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
            <small style="color:var(--text-muted); margin-top:4px; display:block; font-size: 0.75rem;" data-tr="specific_product_desc">Choose if this promo applies to specific products or all.</small>
        </div>
        
        <div class="form-group" style="margin-bottom: 0; grid-column: span 2;">
            <label class="form-label" style="visibility: hidden;">&nbsp;</label>
            <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 10px 14px;">
                <span data-tr="save_promo">Save Promo</span>
            </button>
            <small style="visibility: hidden; margin-top:4px; display:block; font-size: 0.75rem;">&nbsp;</small>
        </div>
    </form>
</div>

<div class="card">
    <h3 style="margin-top:0; margin-bottom: 16px; color:var(--text-main); font-size:1.125rem;" data-tr="active_promos_list">Active Promos List</h3>
    <table>
        <thead>
            <tr>
                <th data-tr="promo_code">Promo Code</th>
                <th data-tr="type">Type</th>
                <th data-tr="discount_value">Discount Value</th>
                <th>Conditions</th>
                <th data-tr="action">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($promos as $promo)
            <tr>
                <td style="font-weight:700; color:var(--text-main); font-size:1rem; font-family: monospace;">{{ $promo->code }}</td>
                <td>
                    @if($promo->type == 'percent')
                        <span class="badge badge-warning" data-tr="percentage">Percentage</span>
                    @else
                        <span class="badge badge-success" data-tr="fixed_discount">Fixed Discount</span>
                    @endif
                </td>
                <td style="font-weight: 600;">
                    @if($promo->type == 'percent')
                        {{ $promo->value }}% 
                        @if($promo->max_discount > 0)
                            <br><small class="text-muted">(Max: Rp {{ number_format($promo->max_discount, 0, ',', '.') }})</small>
                        @endif
                    @else
                        Rp {{ number_format($promo->value, 0, ',', '.') }}
                    @endif
                </td>
                <td style="font-size: 0.85rem;">
                    <div><strong>Min Qty:</strong> {{ $promo->min_qty }}</div>
                    <div><strong>Limit/User:</strong> {{ $promo->usage_limit_per_user == 0 ? 'Unlimited' : $promo->usage_limit_per_user }}</div>
                    <div><strong>Product:</strong> {{ $promo->product ? $promo->product->name : 'All' }}</div>
                </td>
                <td>
                    <form action="{{ route('admin.promos.delete', $promo->id) }}" method="POST" onsubmit="return confirm('Delete this promo?');">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger"><span data-tr="delete">Delete</span></button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" style="text-align:center; padding:40px; color:var(--text-muted);" data-tr="no_active_promos">No active promos.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection