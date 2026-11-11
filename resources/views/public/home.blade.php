@extends('layouts.public')

@section('title', setting('store_name').' | Warung makan rumahan di D\'LAS Karangreja, Purbalingga')
@section('description', 'Lihat menu, harga, dan promo '.setting('store_name').'. Pesan lewat website dan lanjutkan ke WhatsApp, atau datang langsung ke warung kami.')

@push('head')
    @php
        $ld = [
            '@context' => 'https://schema.org',
            '@type' => 'Restaurant',
            'name' => setting('store_name'),
            'description' => setting('tagline'),
            'image' => asset('images/hero.webp'),
            'url' => url('/'),
            'telephone' => '+'.wa_number(setting('wa_number')),
            'address' => setting('address'),
            'openingHours' => setting('open_hours'),
            'servesCuisine' => 'Indonesia',
        ];
    @endphp
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
@endpush

@section('content')
<section class="fb-hero py-5">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <p class="fb-tag mb-2">Selamat datang</p>
                <h1 class="mb-3">{{ setting('store_name') }}</h1>
                <p class="lead text-muted mb-4">{{ setting('tagline') }}</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('menu') }}" class="btn btn-primary btn-lg"><i class="bi bi-journal-text"></i> Lihat menu dan pesan</a>
                    <a href="https://wa.me/{{ wa_number(setting('wa_number')) }}" target="_blank" rel="noopener" class="btn btn-outline-primary btn-lg"><i class="bi bi-whatsapp"></i> Tanya lewat WhatsApp</a>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-4 small text-muted">
                    <span><i class="bi bi-clock text-berry"></i> {{ setting('open_hours') }}</span>
                    @if(setting('halal_cert_number'))
                        <span class="fb-badge-halal"><i class="bi bi-patch-check-fill"></i> Bersertifikat halal</span>
                    @endif
                </div>
            </div>
            <div class="col-lg-6">
                <img src="{{ asset('images/hero.webp') }}" alt="Hidangan {{ setting('store_name') }}" class="fb-hero-img" width="1000" height="750" fetchpriority="high">
            </div>
        </div>
    </div>
</section>

<section class="fb-section" id="tentang">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-md-5">
                <img src="{{ asset('images/about.webp') }}" alt="Warung Fina Berry" class="img-fluid rounded-4 shadow-sm" loading="lazy" width="600" height="450">
            </div>
            <div class="col-md-7">
                <p class="fb-tag mb-1">Tentang kami</p>
                <h2 class="fb-section-title mb-3">Masakan rumahan yang bikin kangen</h2>
                <p class="text-muted">{{ setting('about') }}</p>
                <div class="row g-3 mt-2">
                    <div class="col-6"><i class="bi bi-basket2 text-berry"></i> Bahan segar setiap hari</div>
                    <div class="col-6"><i class="bi bi-fire text-berry"></i> Resep rumahan autentik</div>
                    <div class="col-6"><i class="bi bi-cash-coin text-berry"></i> Harga terjangkau</div>
                    <div class="col-6"><i class="bi bi-lightning-charge text-berry"></i> Penyajian cepat</div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="fb-section bg-cream">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <p class="fb-tag mb-1">Pilihan favorit</p>
                <h2 class="fb-section-title mb-0">Menu andalan kami</h2>
            </div>
            <a href="{{ route('menu') }}" class="btn btn-outline-primary">Semua menu <i class="bi bi-arrow-right"></i></a>
        </div>
        <div class="row g-4">
            @foreach($featured as $product)
                <div class="col-6 col-lg-4">@include('partials.product-card', ['product' => $product])</div>
            @endforeach
        </div>
    </div>
</section>

@if($promos->isNotEmpty())
<section class="fb-section">
    <div class="container">
        <p class="fb-tag mb-1">Sedang berlangsung</p>
        <h2 class="fb-section-title mb-4">Promo</h2>
        <div class="row g-4">
            @foreach($promos as $promo)
                <div class="col-md-6 col-lg-4">
                    <div class="fb-promo p-3 h-100">
                        @if($promo->image_url)
                            <img src="{{ $promo->image_url }}" alt="{{ $promo->title }}" class="img-fluid rounded-3 mb-3" loading="lazy">
                        @endif
                        <h3 class="h5">{{ $promo->title }}</h3>
                        <p class="small text-muted mb-2">{{ $promo->description }}</p>
                        <span class="badge text-bg-warning">Sampai {{ $promo->ends_on->translatedFormat('d F Y') }}</span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@if($photos->isNotEmpty())
<section class="fb-section {{ $promos->isNotEmpty() ? 'bg-cream' : '' }}">
    <div class="container">
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <p class="fb-tag mb-1">Galeri</p>
                <h2 class="fb-section-title mb-0">Momen lezat kami</h2>
            </div>
            <a href="{{ route('gallery') }}" class="btn btn-outline-primary">Lihat semua</a>
        </div>
        <div class="row g-3 fb-gallery">
            @foreach($photos as $photo)
                <div class="col-6 col-md-4"><img src="{{ $photo->image_url }}" alt="{{ $photo->caption ?: 'Foto '.setting('store_name') }}" loading="lazy" width="400" height="400"></div>
            @endforeach
        </div>
    </div>
</section>
@endif

<section class="fb-section">
    <div class="container">
        <div class="row g-4 align-items-stretch">
            <div class="col-lg-5">
                <p class="fb-tag mb-1">Kunjungi kami</p>
                <h2 class="fb-section-title mb-3">Lokasi dan jam buka</h2>
                <p><i class="bi bi-geo-alt text-berry"></i> {{ setting('address') }}</p>
                <p><i class="bi bi-clock text-berry"></i> {{ setting('open_hours') }}</p>
                <p><i class="bi bi-telephone text-berry"></i> +{{ wa_number(setting('wa_number')) }}</p>
                <a href="{{ route('contact') }}" class="btn btn-primary">Halaman kontak</a>
            </div>
            <div class="col-lg-7">
                <iframe class="fb-map" title="Peta lokasi {{ setting('store_name') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                        src="https://www.google.com/maps?q={{ urlencode(setting('maps_query')) }}&output=embed"></iframe>
            </div>
        </div>
    </div>
</section>
@endsection
