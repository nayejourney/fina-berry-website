@extends('layouts.public')

@section('title', 'Menu dan Harga | '.setting('store_name'))
@section('description', 'Daftar menu dan harga '.setting('store_name').': bakso, soto, paket ayam, ikan, minuman, dan cemilan. Pesan online lewat keranjang.')

@section('content')
<section class="container py-5">
    <p class="fb-tag mb-1">Katalog</p>
    <h1 class="fb-section-title mb-4">Menu dan harga</h1>

    <div class="fb-scroll-x mb-4">
        <a href="{{ route('menu') }}" class="fb-chip {{ $aktif ? '' : 'active' }}">Semua</a>
        @foreach($categories as $c)
            <a href="{{ route('menu', ['kategori' => $c->slug]) }}" class="fb-chip {{ $aktif && $aktif->id === $c->id ? 'active' : '' }}">{{ $c->name }}</a>
        @endforeach
    </div>

    @if($products->isEmpty())
        <div class="alert alert-light border">Belum ada menu pada kategori ini.</div>
    @else
        @php $grup = $products->groupBy(fn ($p) => $p->category->name ?? 'Lainnya'); @endphp
        @foreach($grup as $nama => $items)
            <h2 class="h4 mt-4 mb-3">{{ $nama }}</h2>
            <div class="row g-3 g-md-4">
                @foreach($items as $product)
                    <div class="col-6 col-md-4 col-lg-3">@include('partials.product-card', ['product' => $product])</div>
                @endforeach
            </div>
        @endforeach
    @endif
</section>
@endsection
