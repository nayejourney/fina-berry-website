@extends('layouts.public')

@section('title', 'Promo | '.setting('store_name'))
@section('description', 'Promo yang sedang berlangsung di '.setting('store_name').'.')

@section('content')
<section class="container py-5">
    <p class="fb-tag mb-1">Hemat lebih banyak</p>
    <h1 class="fb-section-title mb-4">Promo</h1>

    @forelse($promos as $promo)
        <div class="fb-promo p-3 p-md-4 mb-3">
            <div class="row g-3 align-items-center">
                @if($promo->image_url)
                    <div class="col-md-4"><img src="{{ $promo->image_url }}" alt="{{ $promo->title }}" class="img-fluid rounded-3" loading="lazy"></div>
                @endif
                <div class="{{ $promo->image_url ? 'col-md-8' : 'col-12' }}">
                    <h2 class="h4">{{ $promo->title }}</h2>
                    <p class="text-muted">{{ $promo->description }}</p>
                    <span class="badge text-bg-warning">
                        {{ $promo->starts_on->translatedFormat('d F') }} - {{ $promo->ends_on->translatedFormat('d F Y') }}
                    </span>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-light border">Belum ada promo yang sedang berlangsung. Pantau terus halaman ini, ya.</div>
        <a href="{{ route('menu') }}" class="btn btn-primary">Lihat menu</a>
    @endforelse
</section>
@endsection
