@extends('layouts.admin')

@section('title', 'Menu')

@section('content')
@php $owner = auth()->user()->isOwnerOrAbove(); @endphp
<div class="d-flex flex-wrap gap-2 mb-3">
    <form method="GET" class="d-flex flex-wrap gap-2 me-auto">
        <select name="kategori" class="form-select form-select-sm w-auto">
            <option value="">Semua kategori</option>
            @foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('kategori') == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Cari menu" class="form-control form-control-sm w-auto">
        <div class="form-check d-flex align-items-center gap-1 mb-0">
            <input class="form-check-input mt-0" type="checkbox" name="status" value="nonaktif" id="nonaktif" @checked(request('status') === 'nonaktif')>
            <label class="form-check-label small" for="nonaktif">Hanya nonaktif</label>
        </div>
        <button class="btn btn-sm btn-primary">Filter</button>
    </form>
    @if($owner)<a href="{{ route('admin.products.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah menu</a>@endif
</div>

<div class="ad-card p-3">
    @unless($owner)<p class="small text-muted">Anda dapat menandai menu habis atau tersedia. Perubahan harga dan data menu dilakukan Pemilik.</p>@endunless
    <div class="table-responsive">
        <table class="table table-hover align-middle table-nowrap mb-0">
            <thead><tr><th></th><th>Menu</th><th>Kategori</th><th class="text-end">Harga</th>@if($owner)<th class="text-end">HPP</th>@endif<th>Ketersediaan</th><th></th></tr></thead>
            <tbody>
            @forelse($products as $p)
                <tr class="{{ $p->is_active ? '' : 'table-secondary' }}">
                    <td style="width:56px">
                        @if($p->image_url)<img src="{{ $p->image_url }}" alt="" width="48" height="48" class="rounded" style="object-fit:cover">@endif
                    </td>
                    <td class="fw-semibold">{{ $p->name }} @unless($p->is_active)<span class="badge text-bg-secondary">Nonaktif</span>@endunless @if($p->is_featured)<span class="badge text-bg-warning">Unggulan</span>@endif</td>
                    <td>{{ $p->category->name }}</td>
                    <td class="text-end">{{ rupiah($p->price) }}</td>
                    @if($owner)<td class="text-end text-muted">{{ rupiah($p->cost_price) }}</td>@endif
                    <td>
                        @if($p->is_active)
                        <form method="POST" action="{{ route('admin.products.toggle', $p) }}">@csrf @method('PATCH')
                            <button class="btn btn-sm {{ $p->is_available ? 'btn-outline-success' : 'btn-danger' }}">
                                {{ $p->is_available ? 'Tersedia' : 'Habis' }} <i class="bi bi-arrow-repeat"></i>
                            </button>
                        </form>
                        @else - @endif
                    </td>
                    <td class="text-end">
                        @if($owner)
                            <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Hapus menu ini? Menu yang pernah dipesan hanya dinonaktifkan.')">@csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada menu.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
