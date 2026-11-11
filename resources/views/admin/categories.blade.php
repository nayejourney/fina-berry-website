@extends('layouts.admin')

@section('title', 'Kategori menu')

@section('content')
<div class="row g-3">
    <div class="col-lg-8">
        <div class="ad-card p-3">
            <table class="table align-middle mb-0">
                <thead><tr><th>Nama</th><th style="width:110px">Urutan</th><th>Menu</th><th></th></tr></thead>
                <tbody>
                @foreach($categories as $c)
                    <tr>
                        <td><input type="text" name="name" form="upd{{ $c->id }}" value="{{ $c->name }}" class="form-control form-control-sm" maxlength="60" required></td>
                        <td><input type="number" name="sort_order" form="upd{{ $c->id }}" value="{{ $c->sort_order }}" class="form-control form-control-sm" min="0"></td>
                        <td>{{ $c->products_count }}</td>
                        <td class="text-end text-nowrap">
                            <button type="submit" form="upd{{ $c->id }}" class="btn btn-sm btn-outline-primary">Simpan</button>
                            <button type="submit" form="delcat{{ $c->id }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus kategori ini?')"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            @foreach($categories as $c)
                <form id="upd{{ $c->id }}" method="POST" action="{{ route('admin.categories.update', $c) }}">@csrf @method('PUT')</form>
                <form id="delcat{{ $c->id }}" method="POST" action="{{ route('admin.categories.destroy', $c) }}">@csrf @method('DELETE')</form>
            @endforeach
        </div>
    </div>
    <div class="col-lg-4">
        <form method="POST" action="{{ route('admin.categories.store') }}" class="ad-card p-3">@csrf
            <h2 class="h6">Tambah kategori</h2>
            <input type="text" name="name" class="form-control mb-2" placeholder="Nama kategori" maxlength="60" required>
            <input type="number" name="sort_order" class="form-control mb-2" placeholder="Urutan (angka kecil tampil lebih dulu)" min="0">
            <button class="btn btn-primary">Tambah</button>
        </form>
    </div>
</div>
@endsection
