@extends('layouts.admin')

@section('title', 'QR Code katalog')

@section('content')
<div class="ad-card p-4 text-center mx-auto" style="max-width: 460px;">
    <h2 class="h4 mb-1">{{ setting('store_name') }}</h2>
    <p class="text-muted mb-3">Pindai untuk melihat menu dan memesan</p>
    <div id="qr" class="d-inline-block p-2 bg-white"></div>
    <div class="small text-muted mt-2 text-break">{{ $url }}</div>
    <div class="mt-3 no-print d-flex justify-content-center gap-2">
        <button class="btn btn-dark" onclick="window.print()"><i class="bi bi-printer"></i> Cetak</button>
        <button class="btn btn-outline-dark" id="unduh"><i class="bi bi-download"></i> Unduh PNG</button>
    </div>
    <p class="small text-muted mt-3 mb-0 no-print">Tempel di spanduk, kemasan, atau meja. Pastikan alamat di atas memakai domain asli sebelum dicetak (atur APP_URL di .env).</p>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    (function () {
        var el = document.getElementById('qr');
        new QRCode(el, { text: @json($url), width: 280, height: 280, correctLevel: QRCode.CorrectLevel.H });
        document.getElementById('unduh').addEventListener('click', function () {
            var canvas = el.querySelector('canvas');
            if (!canvas) return;
            var a = document.createElement('a');
            a.href = canvas.toDataURL('image/png'); a.download = 'qr-katalog-fina-berry.png'; a.click();
        });
    })();
</script>
@endpush
