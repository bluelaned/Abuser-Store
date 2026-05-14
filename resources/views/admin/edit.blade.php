@extends('admin.layouts.app')

@section('title', 'Edit Produk: ' . $product->name)

@section('content')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

<style>
    .gallery-preview { display: flex; gap: 16px; flex-wrap: wrap; margin-top: 12px; }
    .img-card { position: relative; width: 120px; height: 80px; border-radius: 8px; border: 1px solid var(--border-color); overflow: hidden; background: rgba(0,0,0,0.2); }
    .img-card img { width: 100%; height: 100%; object-fit: cover; }
    .img-card input[type="checkbox"] { display: none; }
    .img-card label { position: absolute; inset: 0; background: rgba(239, 68, 68, 0); margin: 0; cursor: pointer; transition: 0.2s; display: flex; align-items: center; justify-content: center; }
    .img-card label span { display: none; color: white; font-weight: 600; font-size: 1.25rem; text-shadow: 0 1px 2px rgba(0,0,0,0.5); }
    .img-card label:hover { background: rgba(239, 68, 68, 0.5); }
    .img-card label:hover span { display: block; }
    .img-card input[type="checkbox"]:checked + label { background: rgba(239, 68, 68, 0.9); }
    .img-card input[type="checkbox"]:checked + label span { display: block; content: "Dihapus"; font-size: 0.875rem; }
    /* CKEditor Dark Theme */
    .ck-editor__editable { min-height: 250px; color: var(--text-main) !important; background: rgba(0,0,0,0.2) !important; border-color: var(--border-color) !important; }
    .ck.ck-editor__main>.ck-editor__editable { background: rgba(0,0,0,0.2) !important; border-color: var(--border-color) !important; }
    .ck.ck-toolbar { background: var(--bg-card) !important; border-color: var(--border-color) !important; }
    .ck.ck-button { color: var(--text-main) !important; }
    .ck.ck-button:hover { background: rgba(255,255,255,0.1) !important; }
    .ck-button__icon * { fill: var(--text-main) !important; }
</style>

<div class="header-actions" style="margin-bottom: 16px;">
    <h1>Edit Produk: {{ $product->name }}</h1>
</div>
<a href="{{ route('admin.dashboard') }}" style="display: inline-flex; align-items: center; gap: 8px; color: var(--text-muted); text-decoration: none; font-size: 0.875rem; font-weight: 500; margin-bottom: 24px;">
    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
    </svg>
    Kembali ke Dashboard
</a>

@if ($errors->any())
    <div class="alert alert-danger" style="background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card" style="max-width: 900px;">
    <form action="{{ route('admin.update', $product->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label class="form-label">Nama Produk</label>
            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; padding: 24px; background: rgba(0,0,0,0.2); border: 1px dashed var(--border-color); border-radius: 8px; margin-bottom: 24px;">
            <div>
                <label class="form-label" style="color: var(--primary);">1. Ganti Gambar Cover (Utama)</label>
                <input type="file" name="image" class="form-control" accept="image/*" style="padding: 8px;">
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 4px;">Kosongkan jika tidak ingin mengganti cover.</span>
                
                @if($product->image)
                    <div style="margin-top: 16px;">
                        <span style="font-size: 0.75rem; font-weight: 500; color:var(--text-main);">Cover Saat Ini:</span><br>
                        <img src="{{ asset('storage/' . $product->image) }}" style="height: 80px; width: 120px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border-color); margin-top: 8px;">
                    </div>
                @endif
            </div>

            <div>
                <label class="form-label" style="color: var(--success);">2. Tambah Gambar Slide (Carousel)</label>
                <input type="file" name="slider_images[]" class="form-control" accept="image/*" multiple style="padding: 8px;">
                <span style="font-size: 0.75rem; color: var(--text-muted); display: block; margin-top: 4px;">Bisa pilih > 1 file. Gambar baru akan DITAMBAHKAN ke slide.</span>

                @if($product->images && $product->images->count() > 0)
                    <div style="margin-top: 16px;">
                        <span style="font-size: 0.75rem; font-weight: 500; color:var(--text-main);">Slide Saat Ini (Klik gambar untuk menghapus):</span>
                        <div class="gallery-preview">
                            @foreach($product->images as $img)
                                <div class="img-card" title="Klik untuk hapus gambar ini">
                                    <input type="checkbox" name="delete_sliders[]" value="{{ $img->id }}" id="del_{{ $img->id }}">
                                    <label for="del_{{ $img->id }}"><span>✕</span></label>
                                    <img src="{{ asset('storage/' . $img->image_path) }}">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Tipe Produk</label>
            <select name="type" class="form-control" required>
                <option value="external" {{ $product->type == 'external' ? 'selected' : '' }}>EXTERNAL</option>
                <option value="internal" {{ $product->type == 'internal' ? 'selected' : '' }}>INTERNAL</option>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 24px;">
            <label class="form-label">Deskripsi Produk</label>
            <textarea name="description" id="editor">{{ $product->description }}</textarea>
        </div>

        <div style="background: rgba(0,0,0,0.2); padding: 24px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 24px;">
            <label class="form-label" style="color:var(--text-main); font-weight:600; margin-bottom:16px; border-bottom:1px solid var(--border-color); padding-bottom:8px;">💰 EDIT HARGA & DURASI (FIXED PRICE)</label>
            
            <div id="variant-container">
                @foreach($product->variants as $index => $variant)
                    <div style="display: flex; gap: 12px; margin-bottom: 12px; align-items: center;">
                        <input type="hidden" name="variant_ids[]" value="{{ $variant->id }}">
                        
                        <input type="text" name="durations[]" class="form-control" value="{{ $variant->duration }}" placeholder="Durasi" style="flex: 2; margin-bottom: 0;" required>
                        <input type="number" name="prices_amount[]" class="form-control" value="{{ $variant->price_amount ?? $variant->price_usd }}" step="0.0001" placeholder="Amount" style="flex: 2; margin-bottom: 0;" min="0" required>
                        <select name="currencies[]" class="form-control" style="flex: 1.2; margin-bottom: 0; padding: 10px 8px;">
                            @foreach(['USD' => 'USD ($)', 'IDR' => 'IDR (Rp)', 'EUR' => 'EUR (€)', 'GBP' => 'GBP (£)', 'MYR' => 'MYR (RM)', 'SGD' => 'SGD (S$)', 'THB' => 'THB (฿)', 'JPY' => 'JPY (¥)', 'AUD' => 'AUD (A$)'] as $code => $label)
                                <option value="{{ $code }}" {{ ($variant->currency ?? 'USD') === $code ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        
                        @if($index == 0)
                            <div style="width: 40px;"></div> 
                        @else
                            <button type="button" class="btn btn-danger" onclick="removeVariant(this)" style="padding: 8px 12px;">✕</button>
                        @endif
                    </div>
                @endforeach
            </div>

            <button type="button" class="btn btn-sm btn-secondary" onclick="addVariant()" style="margin-top: 8px;">+ Tambah Varian Baru</button>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; font-size: 1rem; justify-content: center;">Simpan Semua Perubahan</button>
    </form>
</div>

<script>
    ClassicEditor.create(document.querySelector('#editor')).catch(error => console.error(error));

    function addVariant() {
        const html = `
            <div style="display: flex; gap: 12px; margin-bottom: 12px; align-items: center;">
                <input type="hidden" name="variant_ids[]" value="">
                <input type="text" name="durations[]" class="form-control" placeholder="Duration" style="flex: 2; margin-bottom: 0;" required>
                <input type="number" name="prices_amount[]" class="form-control" step="0.0001" placeholder="Amount" style="flex: 2; margin-bottom: 0;" min="0" required>
                <select name="currencies[]" class="form-control" style="flex: 1.2; margin-bottom: 0; padding: 10px 8px;">
                    <option value="USD">USD ($)</option>
                    <option value="IDR">IDR (Rp)</option>
                    <option value="EUR">EUR (€)</option>
                    <option value="GBP">GBP (£)</option>
                    <option value="MYR">MYR (RM)</option>
                    <option value="SGD">SGD (S$)</option>
                    <option value="THB">THB (฿)</option>
                    <option value="JPY">JPY (¥)</option>
                    <option value="AUD">AUD (A$)</option>
                </select>
                <button type="button" class="btn btn-danger" onclick="removeVariant(this)" style="padding: 8px 12px;">✕</button>
            </div>
        `;
        document.getElementById('variant-container').insertAdjacentHTML('beforeend', html);
    }

    function removeVariant(btn) { 
        btn.parentElement.remove(); 
    }
</script>
@endsection