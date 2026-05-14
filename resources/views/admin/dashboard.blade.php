@extends('admin.layouts.app')

@section('title', 'Active Products')

@section('content')
<div class="header-actions">
    <h1 data-tr="active_products">Active Products</h1>
    <button type="button" onclick="openCreateModal()" class="btn btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        <span data-tr="add_new">Add New</span>
    </button>
</div>

<div class="card" id="adminTableCard">
    <table>
        <thead>
            <tr>
                <th width="80" data-tr="image">Image</th>
                <th data-tr="product_info">Product Info</th>
                <th data-tr="total_packages">Total Packages</th>
                <th data-tr="status">Status</th>
                <th data-tr="action">Action</th>
            </tr>
        </thead>
        <tbody id="skeletonRows">
            @for($s = 0; $s < 5; $s++)
            <tr class="sk-tr">
                <td><div class="skeleton sk-avatar" style="border-radius:6px;"></div></td>
                <td>
                    <div class="skeleton sk-cell w80" style="margin-bottom:6px;"></div>
                    <div class="skeleton sk-cell w50"></div>
                </td>
                <td><div class="skeleton sk-cell w30"></div></td>
                <td><div class="skeleton sk-cell w30"></div></td>
                <td><div class="skeleton sk-cell w50"></div></td>
            </tr>
            @endfor
        </tbody>
        <tbody id="realRows" style="display:none;">
            @forelse($products as $p)
            <tr>
                <td>
                    @if($p->image) 
                        <img src="{{ asset('storage/' . $p->image) }}" class="img-thumb" alt="{{ $p->name }}">
                    @else 
                        <div class="img-thumb">NoImg</div> 
                    @endif
                </td>
                <td>
                    <div style="font-weight:600; font-size:0.95rem;">{{ $p->name }}</div>
                    <div style="font-size:0.8rem; color:var(--text-muted); margin-top:2px;">ID: #{{ $p->id }}</div>
                </td>
                <td>{{ $p->variants->count() }} <span data-tr="variants">Variants</span></td>
                <td><span class="badge badge-success">Active</span></td>
                <td>
                    <div style="display:flex; gap:8px;">
                        <a href="{{ route('admin.vouchers.index', $p->id) }}" class="btn btn-sm btn-secondary" style="color:var(--primary); border-color:var(--primary);"><span data-tr="stock">Stock</span></a>
                        <a href="{{ route('admin.edit', $p->id) }}" class="btn btn-sm btn-secondary"><span data-tr="edit">Edit</span></a>
                        <form action="{{ route('admin.delete', $p->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Hapus produk ini?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><span data-tr="delete">Delete</span></button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center; padding:40px; color:var(--text-muted);" data-tr="no_products">
                    No products yet. Please add a new product.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

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
        }, 500);
    });
</script>

<!-- ══════════════════════════════════════════════ -->
<!-- MODAL: TAMBAH PRODUK - REDESIGNED             -->
<!-- ══════════════════════════════════════════════ -->
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<style>
    /* ── Modal Overlay ── */
    .modal {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.65);
        z-index: 999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(8px);
        padding: 20px;
    }
    .modal.active { display: flex; }

    /* ── Modal Box ── */
    .modal-box {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 16px;
        width: 100%;
        max-width: 820px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 24px 60px rgba(0,0,0,0.3);
        animation: modalIn 0.28s cubic-bezier(0.22,1,0.36,1) both;
        scrollbar-width: thin;
        scrollbar-color: var(--border-color) transparent;
    }
    @keyframes modalIn {
        from { opacity:0; transform: scale(0.95) translateY(16px); }
        to   { opacity:1; transform: scale(1)    translateY(0); }
    }

    /* ── Modal Header ── */
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 24px 28px 20px;
        border-bottom: 1px solid var(--border-color);
        position: sticky;
        top: 0;
        background: var(--bg-card);
        z-index: 10;
        border-radius: 16px 16px 0 0;
    }
    .modal-header-left {
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .modal-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: rgba(37,99,235,.12);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .modal-title { font-size: 1.1rem; font-weight: 700; color: var(--text-main); }
    .modal-subtitle { font-size: 0.78rem; color: var(--text-muted); margin-top: 1px; }
    .modal-close {
        width: 36px; height: 36px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
        font-size: 1.2rem;
        display: flex; align-items: center; justify-content: center;
        transition: .2s;
    }
    .modal-close:hover { background: var(--border-color); color: var(--text-main); }

    /* ── Modal Body ── */
    .modal-body { padding: 28px; }

    /* ── Section Label ── */
    .section-label {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 14px;
        margin-top: 28px;
    }
    .section-label:first-child { margin-top: 0; }
    .section-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border-color);
    }

    /* ── Upload Zones ── */
    .upload-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        margin-bottom: 20px;
    }
    .upload-zone {
        border: 2px dashed var(--border-color);
        border-radius: 12px;
        padding: 20px 16px;
        text-align: center;
        cursor: pointer;
        transition: .2s;
        background: var(--bg-body);
        position: relative;
    }
    .upload-zone:hover { border-color: var(--primary); background: rgba(37,99,235,.04); }
    .upload-zone.cover-zone:hover  { border-color: var(--primary); }
    .upload-zone.slider-zone:hover { border-color: var(--success, #10b981); }
    .upload-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }
    .upload-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        margin: 0 auto 10px;
        display: flex; align-items: center; justify-content: center;
    }
    .upload-zone .upload-title { font-size: .85rem; font-weight: 700; color: var(--text-main); margin-bottom: 3px; }
    .upload-zone .upload-hint  { font-size: .75rem; color: var(--text-muted); line-height: 1.4; }
    .upload-filename { font-size: .75rem; color: var(--primary); margin-top: 8px; font-weight: 600; word-break: break-all; }

    /* ── Form controls ── */
    .form-row { display: grid; gap: 16px; margin-bottom: 16px; }
    .form-row.cols-2 { grid-template-columns: 1fr 1fr; }
    .modal-form .form-group { margin-bottom: 16px; }
    .modal-form .form-label {
        display: block;
        font-size: .8rem;
        font-weight: 600;
        color: var(--text-muted);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: .5px;
    }
    .modal-form .form-control {
        width: 100%;
        padding: 11px 14px;
        border: 1px solid var(--border-color);
        border-radius: 8px;
        font-size: .9rem;
        color: var(--text-main);
        background: var(--bg-body);
        transition: border-color .2s, box-shadow .2s;
        font-family: 'Inter', sans-serif;
    }
    .modal-form .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 3px rgba(37,99,235,.12);
    }

    /* ── Variant Table ── */
    .variant-table-wrap {
        background: var(--bg-body);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        overflow: hidden;
    }
    .variant-table-header {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 40px;
        gap: 0;
        padding: 10px 16px;
        background: var(--border-color);
        font-size: .72rem;
        font-weight: 700;
        letter-spacing: .6px;
        text-transform: uppercase;
        color: var(--text-muted);
    }
    .variant-row {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr 40px;
        gap: 12px;
        padding: 12px 16px;
        align-items: center;
        border-top: 1px solid var(--border-color);
    }
    .variant-row:first-of-type { border-top: none; }
    .variant-row .form-control { margin-bottom: 0; }
    .remove-variant-btn {
        width: 30px; height: 30px;
        border-radius: 6px;
        border: 1px solid var(--border-color);
        background: transparent;
        color: var(--text-muted);
        cursor: pointer;
        font-size: .9rem;
        display: flex; align-items: center; justify-content: center;
        transition: .2s;
    }
    .remove-variant-btn:hover { border-color: var(--danger); color: var(--danger); background: rgba(239,68,68,.06); }

    .add-variant-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 16px;
        border-radius: 0 0 12px 12px;
        border: none;
        border-top: 1px dashed var(--border-color);
        background: transparent;
        color: var(--primary);
        cursor: pointer;
        font-size: .85rem;
        font-weight: 600;
        width: 100%;
        transition: .2s;
    }
    .add-variant-btn:hover { background: rgba(37,99,235,.06); }

    /* ── CKEditor fix ── */
    .ck-editor__editable { min-height: 180px; color: #000 !important; background: #fff !important; }
    .ck.ck-editor__main>.ck-editor__editable { background: #fff !important; }
    .ck.ck-toolbar { border-radius: 8px 8px 0 0 !important; }
    .ck.ck-editor__editable { border-radius: 0 0 8px 8px !important; }

    /* ── Modal Footer ── */
    .modal-footer {
        padding: 20px 28px;
        border-top: 1px solid var(--border-color);
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        position: sticky;
        bottom: 0;
        background: var(--bg-card);
        border-radius: 0 0 16px 16px;
    }
    .btn-cancel {
        padding: 10px 20px;
        border-radius: 8px;
        border: 1px solid var(--border-color);
        background: transparent;
        color: var(--text-muted);
        font-size: .9rem;
        font-weight: 600;
        cursor: pointer;
        transition: .2s;
    }
    .btn-cancel:hover { background: var(--border-color); color: var(--text-main); }
    .btn-submit {
        padding: 10px 28px;
        border-radius: 8px;
        border: none;
        background: var(--primary);
        color: #fff;
        font-size: .9rem;
        font-weight: 700;
        cursor: pointer;
        transition: .2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .btn-submit:hover { background: var(--primary-hover); transform: translateY(-1px); box-shadow: 0 4px 12px rgba(37,99,235,.3); }
</style>

<div id="createProductModal" class="modal">
    <div class="modal-box">

        <!-- Header -->
        <div class="modal-header">
            <div class="modal-header-left">
                <div class="modal-icon">
                    <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M12 5v14M5 12h14"/>
                    </svg>
                </div>
                <div>
                    <div class="modal-title" data-tr="add_new_product">Add New Product</div>
                    <div class="modal-subtitle" data-tr="fill_product_info">Fill in all product information below</div>
                </div>
            </div>
            <button type="button" class="modal-close" onclick="closeCreateModal()">✕</button>
        </div>

        <!-- Body -->
        <div class="modal-body">
            <form action="{{ route('admin.store') }}" method="POST" enctype="multipart/form-data" class="modal-form">
                @csrf

                <!-- ── INFORMASI DASAR ── -->
                <div class="section-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M13 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                    <span data-tr="basic_info">Basic Information</span>
                </div>

                <div class="form-row cols-2">
                    <div class="form-group">
                        <label class="form-label" data-tr="product_name">Product Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g: Fatality Premium" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" data-tr="product_type">Product Type</label>
                        <select name="type" class="form-control" required>
                            <option value="external">EXTERNAL</option>
                            <option value="internal">INTERNAL</option>
                        </select>
                    </div>
                </div>

                <!-- ── GAMBAR ── -->
                <div class="section-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    <span data-tr="upload_images">Upload Images</span>
                </div>

                <div class="upload-grid">
                    <!-- Cover -->
                    <div class="upload-zone cover-zone">
                        <input type="file" name="image" accept="image/*" id="coverInput"
                               onchange="showFileName(this,'coverName')">
                        <div class="upload-icon" style="background:rgba(37,99,235,.1);">
                            <svg width="20" height="20" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        </div>
                        <div class="upload-title" data-tr="cover_image">Cover Image (Main)</div>
                        <div class="upload-hint" data-tr="cover_image_hint">Appears on Home/Index</div>
                        <div class="upload-filename" id="coverName" data-tr="not_selected">Not selected</div>
                    </div>
                    <!-- Slider -->
                    <div class="upload-zone slider-zone">
                        <input type="file" name="slider_images[]" accept="image/*" multiple id="sliderInput"
                               onchange="showFileName(this,'sliderName')">
                        <div class="upload-icon" style="background:rgba(16,185,129,.1);">
                            <svg width="20" height="20" fill="none" stroke="#10b981" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                        </div>
                        <div class="upload-title" data-tr="slider_images">Slide Images (Carousel)</div>
                        <div class="upload-hint" data-tr="slider_images_hint">Hold CTRL for multi-select · Appears on Checkout</div>
                        <div class="upload-filename" id="sliderName" data-tr="not_selected">Not selected</div>
                    </div>
                </div>

                <!-- ── DESKRIPSI ── -->
                <div class="section-label">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="17" y1="10" x2="3" y2="10"/><line x1="21" y1="6" x2="3" y2="6"/><line x1="21" y1="14" x2="3" y2="14"/><line x1="17" y1="18" x2="3" y2="18"/></svg>
                    <span data-tr="product_desc">Product Description</span>
                </div>
                <div class="form-group">
                    <textarea name="description" id="editor"></textarea>
                </div>

                <!-- ── HARGA & PAKET ── -->
                <div class="section-label" style="margin-top:24px;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    <span data-tr="price_packages">Price Packages</span>
                </div>

                <div class="variant-table-wrap">
                    <div class="variant-table-header">
                        <span data-tr="duration_name">Duration / Package Name</span>
                        <span data-tr="price_idr">Price IDR (Rp)</span>
                        <span data-tr="price_usd">Price USD ($)</span>
                        <span></span>
                    </div>
                    <div id="variant-container">
                        <div class="variant-row">
                            <input type="text" name="durations[]" class="form-control" placeholder="cth: 1 Bulan" required>
                            <input type="number" name="prices[]" class="form-control" placeholder="cth: 150000" required>
                            <input type="number" name="prices_usd[]" class="form-control" step="0.01" placeholder="cth: 9.99" required>
                            <div style="width:30px;"></div>
                        </div>
                    </div>
                    <button type="button" class="add-variant-btn" onclick="addVariant()">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                        <span data-tr="add_another_package">Add Another Package</span>
                    </button>
                </div>

            </form>
        </div>

        <!-- Footer -->
        <div class="modal-footer">
            <button type="button" class="btn-cancel" onclick="closeCreateModal()"><span data-tr="cancel">Cancel</span></button>
            <button type="button" class="btn-submit" onclick="submitProductForm()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v14z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                </svg>
                <span data-tr="save_publish">Save & Publish</span>
            </button>
        </div>

    </div>
</div>

<script>
    let editorInstance;

    function openCreateModal() {
        document.getElementById('createProductModal').classList.add('active');
        document.body.style.overflow = 'hidden';
        if (!editorInstance) {
            ClassicEditor.create(document.querySelector('#editor'))
                .then(editor => { editorInstance = editor; })
                .catch(err => console.error(err));
        }
    }

    function closeCreateModal() {
        document.getElementById('createProductModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    // Close on backdrop click
    document.getElementById('createProductModal').addEventListener('click', function(e) {
        if (e.target === this) closeCreateModal();
    });

    function submitProductForm() {
        // Sync CKEditor content back to textarea before submit
        if (editorInstance) {
            document.querySelector('[name="description"]').value = editorInstance.getData();
        }
        document.querySelector('.modal-form').submit();
    }

    function addVariant() {
        const row = `<div class="variant-row">
            <input type="text" name="durations[]" class="form-control" placeholder="e.g: 3 Months" required>
            <input type="number" name="prices[]" class="form-control" placeholder="e.g: 400000" required>
            <input type="number" name="prices_usd[]" class="form-control" step="0.01" placeholder="e.g: 24.99" required>
            <button type="button" class="remove-variant-btn" onclick="this.closest('.variant-row').remove()">✕</button>
        </div>`;
        document.getElementById('variant-container').insertAdjacentHTML('beforeend', row);
    }

    function showFileName(input, targetId) {
        const el = document.getElementById(targetId);
        if (input.files.length === 0) {
            el.textContent = 'Not selected';
            // update with current language if needed (handled simply for now)
        } else if (input.files.length === 1) {
            el.textContent = input.files[0].name;
        } else {
            el.textContent = input.files.length + ' files selected';
        }
    }
</script>
@endsection