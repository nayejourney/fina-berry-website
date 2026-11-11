@extends('layouts.admin')

@section('title', $promo->exists ? 'Ubah promo' : 'Tambah promo')

@section('content')
<form method="POST" enctype="multipart/form-data" class="ad-card p-3 p-md-4" style="max-width: 720px;"
      action="{{ $promo->exists ? route('admin.promos.update', $promo) : route('admin.promos.store') }}">
    @csrf
    @if($promo->exists) @method('PUT') @endif
    <div class="mb-3"><label class="form-label" for="title">Judul promo</label>
        <input type="text" id="title" name="title" value="{{ old('title', $promo->title) }}" class="form-control" maxlength="120" required></div>
    <div class="mb-3"><label class="form-label" for="description">Deskripsi</label>
        <textarea id="description" name="description" rows="3" class="form-control" maxlength="600">{{ old('description', $promo->description) }}</textarea></div>
    <div class="row g-3 mb-3">
        <div class="col-md-6"><label class="form-label" for="starts_on">Mulai</label>
            <input type="date" id="starts_on" name="starts_on" value="{{ old('starts_on', optional($promo->starts_on)->toDateString()) }}" class="form-control" required></div>
        <div class="col-md-6"><label class="form-label" for="ends_on">Berakhir</label>
            <input type="date" id="ends_on" name="ends_on" value="{{ old('ends_on', optional($promo->ends_on)->toDateString()) }}" class="form-control" required></div>
    </div>
    <div class="mb-3"><label class="form-label" for="photo">Gambar (opsional)</label>
        <input type="file" id="photo" name="photo" class="form-control" accept="image/jpeg,image/png,image/webp">
        @if($promo->image_url)<img src="{{ $promo->image_url }}" alt="" class="mt-2 rounded" width="160">@endif</div>
    <div class="form-check mb-4"><input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $promo->is_active))><label class="form-check-label" for="is_active">Aktif (tampil selama dalam rentang tanggal)</label></div>
    <button class="btn btn-primary">Simpan</button> <a href="{{ route('admin.promos.index') }}" class="btn btn-light">Batal</a>
</form>
@endsection
