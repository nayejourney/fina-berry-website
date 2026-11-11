<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', setting('store_name').' | '.setting('tagline'))</title>
    <meta name="description" content="@yield('description', \Illuminate\Support\Str::limit(setting('about'), 155))">
    @hasSection('noindex')
        <meta name="robots" content="noindex, nofollow">
    @endif
    <link rel="canonical" href="{{ url()->current() }}">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ setting('store_name') }}">
    <meta property="og:title" content="@yield('title', setting('store_name'))">
    <meta property="og:description" content="@yield('description', \Illuminate\Support\Str::limit(setting('about'), 155))">
    <meta property="og:image" content="{{ asset('images/hero.webp') }}">
    <link rel="icon" href="{{ asset('images/logo.webp') }}" type="image/webp">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    @stack('head')
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top fb-nav">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('home') }}">
            <img src="{{ asset('images/logo.webp') }}" alt="Logo {{ setting('store_name') }}" class="fb-logo" width="38" height="38">
            <span>{{ setting('store_name') }}</span>
        </a>
        <div class="d-flex align-items-center gap-2 order-lg-last">
            <a href="{{ route('cart') }}" class="btn btn-outline-primary position-relative" aria-label="Keranjang">
                <i class="bi bi-bag"></i>
                @if($cartCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $cartCount }}</span>
                @endif
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Buka menu">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
        <div class="collapse navbar-collapse" id="navMain">
            <ul class="navbar-nav mx-lg-auto">
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">Beranda</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('menu') ? 'active' : '' }}" href="{{ route('menu') }}">Menu</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('promo') ? 'active' : '' }}" href="{{ route('promo') }}">Promo</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('gallery') ? 'active' : '' }}" href="{{ route('gallery') }}">Galeri</a></li>
                <li class="nav-item"><a class="nav-link {{ request()->routeIs('contact') ? 'active' : '' }}" href="{{ route('contact') }}">Kontak</a></li>
            </ul>
        </div>
    </div>
</nav>

<main>
    @include('partials.flash')
    @yield('content')
</main>

<footer class="fb-footer pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-5">
                <h2 class="h5 text-white">{{ setting('store_name') }}</h2>
                <p class="small mb-2">{{ setting('tagline') }}</p>
                @if(setting('halal_cert_number'))
                    <span class="fb-badge-halal"><i class="bi bi-patch-check-fill"></i> Halal, No. {{ setting('halal_cert_number') }}</span>
                @endif
            </div>
            <div class="col-md-4 small">
                <div class="mb-2"><i class="bi bi-geo-alt"></i> {{ setting('address') }}</div>
                <div><i class="bi bi-clock"></i> {{ setting('open_hours') }}</div>
            </div>
            <div class="col-md-3 small">
                <div class="mb-2 fw-semibold text-white">Ikuti dan hubungi kami</div>
                <div class="d-flex gap-3 fs-5">
                    <a href="https://wa.me/{{ wa_number(setting('wa_number')) }}" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
                    @if(setting('instagram'))<a href="{{ setting('instagram') }}" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>@endif
                    @if(setting('facebook'))<a href="{{ setting('facebook') }}" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>@endif
                    @if(setting('tiktok'))<a href="{{ setting('tiktok') }}" target="_blank" rel="noopener" aria-label="TikTok"><i class="bi bi-tiktok"></i></a>@endif
                </div>
            </div>
        </div>
        <hr class="border-secondary my-4">
        <div class="d-flex flex-wrap justify-content-between small">
            <span>&copy; {{ date('Y') }} {{ setting('store_name') }}</span>
            <a href="{{ route('login') }}" class="text-secondary">Masuk admin</a>
        </div>
    </div>
</footer>

<a href="https://wa.me/{{ wa_number(setting('wa_number')) }}" class="fb-wa-float" target="_blank" rel="noopener" aria-label="Chat WhatsApp {{ setting('store_name') }}">
    <i class="bi bi-whatsapp"></i>
</a>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
