<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Dashboard') | Admin {{ setting('store_name') }}</title>
    <link rel="icon" href="{{ asset('images/logo.webp') }}" type="image/webp">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
    @stack('head')
</head>
<body>
@php($u = auth()->user())
<div class="d-flex">
    <aside class="offcanvas-lg offcanvas-start ad-sidebar p-3 flex-shrink-0" tabindex="-1" id="adSide">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('admin.dashboard') }}" class="ad-brand">
                <img src="{{ asset('images/logo.webp') }}" alt="">
                <span>Fina Berry<br><small class="fw-normal text-secondary">Admin</small></span>
            </a>
            <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#adSide" aria-label="Tutup"></button>
        </div>
        <nav class="nav flex-column">
            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2"></i> Dashboard</a>

            <div class="nav-section">Operasional</div>
            <a class="nav-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" href="{{ route('admin.orders.index') }}">
                <i class="bi bi-receipt"></i> Pesanan
                @if($waitingCount > 0)<span class="badge bg-warning text-dark ms-auto">{{ $waitingCount }}</span>@endif
            </a>
            <a class="nav-link {{ request()->routeIs('admin.pos*') ? 'active' : '' }}" href="{{ route('admin.pos') }}"><i class="bi bi-calculator"></i> Input di warung</a>
            <a class="nav-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" href="{{ route('admin.products.index') }}"><i class="bi bi-egg-fried"></i> Menu</a>

            @if($u->isOwnerOrAbove())
                <div class="nav-section">Konten dan toko</div>
                <a class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" href="{{ route('admin.categories.index') }}"><i class="bi bi-tags"></i> Kategori</a>
                <a class="nav-link {{ request()->routeIs('admin.promos.*') ? 'active' : '' }}" href="{{ route('admin.promos.index') }}"><i class="bi bi-megaphone"></i> Promo</a>
                <a class="nav-link {{ request()->routeIs('admin.gallery.*') ? 'active' : '' }}" href="{{ route('admin.gallery.index') }}"><i class="bi bi-images"></i> Galeri</a>
                <a class="nav-link {{ request()->routeIs('admin.qr') ? 'active' : '' }}" href="{{ route('admin.qr') }}"><i class="bi bi-qr-code"></i> QR Code</a>
                <a class="nav-link {{ request()->routeIs('admin.settings.*') ? 'active' : '' }}" href="{{ route('admin.settings.edit') }}"><i class="bi bi-gear"></i> Pengaturan toko</a>

                <div class="nav-section">Laporan</div>
                <a class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" href="{{ route('admin.reports.index') }}"><i class="bi bi-bar-chart-line"></i> Laporan penjualan</a>
                <a class="nav-link {{ request()->routeIs('admin.expenses.*') ? 'active' : '' }}" href="{{ route('admin.expenses.index') }}"><i class="bi bi-wallet2"></i> Biaya operasional</a>
            @endif

            @if($u->isSuperAdmin())
                <a class="nav-link {{ request()->routeIs('admin.analytics.*') ? 'active' : '' }}" href="{{ route('admin.analytics.index') }}"><i class="bi bi-graph-up-arrow"></i> Laba rugi dan analitik</a>
            @endif

            @if($u->isOwnerOrAbove())
                <div class="nav-section">Sistem</div>
                <a class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}"><i class="bi bi-people"></i> Akun pengguna</a>
                <a class="nav-link {{ request()->routeIs('admin.audit') ? 'active' : '' }}" href="{{ route('admin.audit') }}"><i class="bi bi-journal-check"></i> Log aktivitas</a>
            @endif

            <div class="nav-section">Lainnya</div>
            <a class="nav-link" href="{{ route('home') }}" target="_blank" rel="noopener"><i class="bi bi-box-arrow-up-right"></i> Lihat website</a>
        </nav>
    </aside>

    <div class="ad-main">
        <header class="bg-white border-bottom px-3 py-2 d-flex align-items-center gap-2">
            <button class="btn btn-outline-secondary d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adSide" aria-controls="adSide" aria-label="Buka menu"><i class="bi bi-list"></i></button>
            <h1 class="h5 mb-0 me-auto">@yield('title', 'Dashboard')</h1>
            <a href="{{ route('admin.account') }}" class="text-decoration-none text-dark small text-end">
                <strong>{{ $u->name }}</strong><br><span class="badge text-bg-secondary">{{ $u->role_label }}</span>
            </a>
            <form method="POST" action="{{ route('logout') }}">@csrf
                <button class="btn btn-outline-danger btn-sm" title="Keluar"><i class="bi bi-box-arrow-right"></i></button>
            </form>
        </header>

        <div id="newOrderBanner" class="alert alert-warning ad-new-banner rounded-0 mb-0 d-none d-flex justify-content-between align-items-center">
            <span><i class="bi bi-bell-fill"></i> <strong>Ada pesanan baru masuk.</strong></span>
            <a href="{{ route('admin.orders.index', ['status' => 'menunggu']) }}" class="btn btn-sm btn-dark">Lihat sekarang</a>
        </div>

        <div class="p-3 p-lg-4">
            @include('partials.flash')
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Penanda pesanan baru: cek berkala tiap 20 detik (FR-14).
    (function () {
        var banner = document.getElementById('newOrderBanner');
        var alerted = null;
        window.fbLatest = null;
        function beep() {
            try {
                var ctx = new (window.AudioContext || window.webkitAudioContext)();
                var o = ctx.createOscillator(); o.frequency.value = 880; o.connect(ctx.destination);
                o.start(); setTimeout(function () { o.stop(); ctx.close(); }, 250);
            } catch (e) {}
        }
        window.fbAckOrders = function (id) { sessionStorage.setItem('fb_last_order_id', id); banner.classList.add('d-none'); };
        banner.querySelector('a').addEventListener('click', function () { if (window.fbLatest !== null) window.fbAckOrders(window.fbLatest); });
        function check() {
            fetch(@json(route('admin.orders.poll')), { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (d) {
                    if (!d) return;
                    window.fbLatest = d.latest_id;
                    var last = sessionStorage.getItem('fb_last_order_id');
                    if (last === null) { sessionStorage.setItem('fb_last_order_id', d.latest_id); return; }
                    if (parseInt(d.latest_id, 10) > parseInt(last, 10)) {
                        banner.classList.remove('d-none');
                        if (alerted !== d.latest_id) { alerted = d.latest_id; beep(); }
                    }
                }).catch(function () {});
        }
        check();
        setInterval(check, 20000);
    })();
</script>
@stack('scripts')
</body>
</html>
