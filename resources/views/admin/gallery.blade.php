@extends('layouts.admin')

@section('title', 'Galeri foto')

@section('content')
<form method="POST" enctype="multipart/form-data" action="{{ route('admin.gallery.store') }}" class="ad-card p-3 mb-3">@csrf
    <h2 class="h6">Tambah foto</h2>
    <div class="row g-2 align-items-end">
        <div class="col-md-4"><input type="file" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp" required></div>
        <div class="col-md-4"><input type="text" name="caption" class="form-control" placeholder="Keterangan (opsional)" maxlength="120"></div>
        <div class="col-md-2"><input type="number" name="sort_order" class="form-control" placeholder="Urutan" min="0"></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Unggah</button></div>
    </div>
</form>

<div class="row g-3">
    @forelse($photos as $ph)
        <div class="col-6 col-md-4 col-xl-3">
            <div class="ad-card p-2 h-100">
                <img src="{{ $ph->image_url }}" alt="{{ $ph->caption }}" class="img-fluid rounded mb-2" style="aspect-ratio:1/1;object-fit:cover;width:100%">
                <form method="POST" action="{{ route('admin.gallery.update', $ph) }}">@csrf @method('PUT')
                    <input type="text" name="caption" value="{{ $ph->caption }}" class="form-control form-control-sm mb-1" placeholder="Keterangan" maxlength="120">
                    <div class="d-flex gap-1">
                        <input type="number" name="sort_order" value="{{ $ph->sort_order }}" class="form-control form-control-sm" min="0" style="width:80px">
                        <button class="btn btn-sm btn-outline-primary">Simpan</button>
                        <button type="submit" form="delph{{ $ph->id }}" class="btn btn-sm btn-outline-danger ms-auto" onclick="return confirm('Hapus foto ini?')"><i class="bi bi-trash"></i></button>
                    </div>
                </form>
                <form id="delph{{ $ph->id }}" method="POST" action="{{ route('admin.gallery.destroy', $ph) }}">@csrf @method('DELETE')</form>
            </div>
        </div>
    @empty
        <div class="col-12"><div class="alert alert-light border">Belum ada foto.</div></div>
    @endforelse
</div>
@endsection
