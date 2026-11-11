@extends('layouts.public')

@section('title', 'Kontak dan Lokasi | '.setting('store_name'))
@section('description', 'Alamat, jam buka, WhatsApp, dan peta lokasi '.setting('store_name').'.')

@section('content')
<section class="container py-5">
    <p class="fb-tag mb-1">Hubungi kami</p>
    <h1 class="fb-section-title mb-4">Kontak dan lokasi</h1>
    <div class="row g-4">
        <div class="col-lg-5">
            <ul class="list-unstyled fs-6">
                <li class="mb-3"><i class="bi bi-geo-alt text-berry"></i> <strong>Alamat</strong><br>{{ setting('address') }}</li>
                <li class="mb-3"><i class="bi bi-clock text-berry"></i> <strong>Jam buka</strong><br>{{ setting('open_hours') }}</li>
                <li class="mb-3"><i class="bi bi-whatsapp text-berry"></i> <strong>WhatsApp</strong><br>
                    <a href="https://wa.me/{{ wa_number(setting('wa_number')) }}" target="_blank" rel="noopener">+{{ wa_number(setting('wa_number')) }}</a></li>
            </ul>
            <div class="d-flex flex-wrap gap-2">
                <a href="https://wa.me/{{ wa_number(setting('wa_number')) }}" target="_blank" rel="noopener" class="btn btn-primary"><i class="bi bi-whatsapp"></i> Chat WhatsApp</a>
                @if(setting('instagram'))<a href="{{ setting('instagram') }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><i class="bi bi-instagram"></i> Instagram</a>@endif
                @if(setting('facebook'))<a href="{{ setting('facebook') }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><i class="bi bi-facebook"></i> Facebook</a>@endif
                @if(setting('tiktok'))<a href="{{ setting('tiktok') }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><i class="bi bi-tiktok"></i> TikTok</a>@endif
                <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode(setting('maps_query')) }}" target="_blank" rel="noopener" class="btn btn-outline-primary"><i class="bi bi-map"></i> Buka di Maps</a>
            </div>
        </div>
        <div class="col-lg-7">
            <iframe class="fb-map" title="Peta lokasi {{ setting('store_name') }}" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
                    src="https://www.google.com/maps?q={{ urlencode(setting('maps_query')) }}&output=embed"></iframe>
        </div>
    </div>
</section>
@endsection
