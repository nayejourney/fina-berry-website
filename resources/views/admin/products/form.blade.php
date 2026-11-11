@extends('layouts.admin')

@section('title', $product->exists ? 'Ubah menu' : 'Tambah menu')

@section('content')
<form method="POST" enctype="multipart/form-data" class="ad-card p-3 p-md-4" style="max-width: 760px;"
      action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
    @csrf
    @if($product->exists) @method('PUT') @endif

    <div class="row g-3">
        <div class="col-md-8">
            <label class="form-label" for="name">Nama menu</label>
            <input type="text" id="name" name="name" value="{{ old('name', $product->name) }}" class="form-control" maxlength="120" required>
        </div>
        <div class="col-md-4">
            <label class="form-label" for="category_id">Kategori</label>
            <select id="category_id" name="category_id" class="form-select" required>
                @foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id', $product->category_id) == $c->id)>{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label" for="description">Deskripsi (opsional)</label>
            <textarea id="description" name="description" rows="2" class="form-control" maxlength="500">{{ old('description', $product->description) }}</textarea>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="price">Harga jual (Rp)</label>
            <input type="number" id="price" name="price" value="{{ old('price', $product->price) }}" class="form-control" min="0" step="1" required>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="cost_price">HPP / modal per porsi (Rp)</label>
            <input type="number" id="cost_price" name="cost_price" value="{{ old('cost_price', $product->cost_price ?? 0) }}" class="form-control" min="0" step="1" required>
            <div class="form-text">Dipakai untuk menghitung laba dan margin (hanya terlihat oleh Pemilik dan Super Admin).</div>
        </div>
        <div class="col-12">
            <label class="form-label" for="photo">Foto menu</label>
            <input type="file" id="photo" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
            <div class="form-text">JPG, PNG, atau WebP, maksimal 5 MB. Foto otomatis diperkecil dan diubah ke WebP.</div>
            @if($product->image_url)<img src="{{ $product->image_url }}" alt="" class="mt-2 rounded" width="120">@endif
        </div>
        <div class="col-12 d-flex flex-wrap gap-4">
            <div class="form-check"><input class="form-check-input" type="checkbox" name="is_available" id="is_available" value="1" @checked(old('is_available', $product->is_available))><label class="form-check-label" for="is_available">Tersedia (bisa dipesan)</label></div>
            <div class="form-check"><input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $product->is_active))><label class="form-check-label" for="is_active">Aktif (tampil di katalog)</label></div>
            <div class="form-check"><input class="form-check-input" type="checkbox" name="is_featured" id="is_featured" value="1" @checked(old('is_featured', $product->is_featured))><label class="form-check-label" for="is_featured">Menu unggulan (tampil di beranda)</label></div>
        </div>
    </div>
    <div class="mt-4 d-flex gap-2">
        <button class="btn btn-primary">Simpan</button>
        <a href="{{ route('admin.products.index') }}" class="btn btn-light">Batal</a>
    </div>
</form>
@endsection
