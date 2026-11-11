@extends('layouts.public')

@section('title', 'Pesanan '.$order->code.' | '.setting('store_name'))
@section('noindex', '1')

@section('content')
<section class="container py-5" style="max-width: 760px;">
    <div class="text-center mb-4">
        <div class="display-5">✅</div>
        <h1 class="h2">Pesanan tersimpan</h1>
        <p class="text-muted mb-1">Kode pesanan Anda</p>
        <div class="fs-3 fw-bold text-berry">{{ $order->code }}</div>
        <span class="badge {{ $order->badge_class }} mt-2">{{ $order->status_label }}</span>
    </div>

    <div class="alert alert-warning">
        <strong>Satu langkah lagi:</strong> kirim pesan WhatsApp ke warung supaya pesanan segera diproses. Pesanan tetap tersimpan walaupun WhatsApp belum terkirim.
    </div>

    <div class="d-grid gap-2 mb-4">
        <a id="btnWa" href="{{ $waUrl }}" target="_blank" rel="noopener" class="btn btn-success btn-lg"><i class="bi bi-whatsapp"></i> Buka WhatsApp</a>
        <button type="button" id="btnCopy" class="btn btn-outline-primary"><i class="bi bi-clipboard"></i> Salin pesan</button>
        <div class="small text-muted text-center">WhatsApp tidak terbuka? Salin pesan lalu kirim manual ke <strong>+{{ wa_number(setting('wa_number')) }}</strong>.</div>
    </div>
    <textarea id="waText" class="visually-hidden" readonly>{{ $waMessage }}</textarea>

    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <h2 class="h5">Ringkasan</h2>
            <dl class="row small mb-3">
                <dt class="col-5">Nama</dt><dd class="col-7">{{ $order->customer_name }}</dd>
                <dt class="col-5">WhatsApp</dt><dd class="col-7">{{ $order->customer_phone }}</dd>
                <dt class="col-5">Pengambilan</dt><dd class="col-7">{{ $order->fulfillment_label }}@if($order->address): {{ $order->address }}@endif</dd>
                <dt class="col-5">Rencana pembayaran</dt><dd class="col-7">{{ $order->payment_method_label }}</dd>
                @if($order->notes)<dt class="col-5">Catatan</dt><dd class="col-7">{{ $order->notes }}</dd>@endif
            </dl>
            <ul class="list-unstyled mb-2">
                @foreach($order->items as $item)
                    <li class="d-flex justify-content-between py-1 border-bottom">
                        <span>{{ $item->product_name }} <span class="text-muted">x{{ $item->quantity }}</span></span>
                        <span>{{ rupiah($item->line_total) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total</span><span class="fb-price">{{ rupiah($order->total) }}</span></div>
        </div>
    </div>

    <p class="small text-muted">Simpan halaman ini untuk melihat status pesanan. Pembayaran dilakukan langsung di warung.</p>
    <a href="{{ route('menu') }}" class="btn btn-link">Kembali ke menu</a>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        var btn = document.getElementById('btnCopy'), area = document.getElementById('waText');
        btn.addEventListener('click', function () {
            var done = function () { btn.innerHTML = '<i class="bi bi-check2"></i> Tersalin'; };
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(area.value).then(done);
            } else {
                area.classList.remove('visually-hidden'); area.select(); document.execCommand('copy');
                area.classList.add('visually-hidden'); done();
            }
        });
        @if(session('auto_wa'))
        // Coba buka WhatsApp otomatis sekali setelah pesanan baru dibuat.
        setTimeout(function () { window.location.href = document.getElementById('btnWa').href; }, 1800);
        @endif
    })();
</script>
@endpush
