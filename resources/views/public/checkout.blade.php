@extends('layouts.public')

@section('title', 'Checkout | '.setting('store_name'))
@section('noindex', '1')

@section('content')
<section class="container py-5">
    <h1 class="fb-section-title mb-4">Checkout</h1>
    <div class="row g-4">
        <div class="col-lg-7">
            <form method="POST" action="{{ route('checkout.store') }}" id="formCheckout" novalidate>
                @csrf
                <input type="hidden" name="idempotency_key" value="{{ $key }}">

                <div class="mb-3">
                    <label for="customer_name" class="form-label">Nama</label>
                    <input type="text" id="customer_name" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" value="{{ old('customer_name') }}" maxlength="80" required autocomplete="name">
                </div>
                <div class="mb-3">
                    <label for="customer_phone" class="form-label">Nomor WhatsApp</label>
                    <input type="tel" id="customer_phone" name="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" value="{{ old('customer_phone') }}" placeholder="081234567890" inputmode="tel" required autocomplete="tel">
                    <div class="form-text">Dipakai warung untuk menghubungi Anda tentang pesanan.</div>
                </div>

                <div class="mb-3">
                    <span class="form-label d-block">Pengambilan</span>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="fulfillment" id="f_ambil" value="ambil" {{ old('fulfillment', 'ambil') === 'ambil' ? 'checked' : '' }}>
                        <label class="form-check-label" for="f_ambil">Ambil di warung</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="fulfillment" id="f_antar" value="antar" {{ old('fulfillment') === 'antar' ? 'checked' : '' }}>
                        <label class="form-check-label" for="f_antar">Diantar</label>
                    </div>
                    <div class="form-text">Ketentuan antar dan area layanan dikonfirmasi warung lewat WhatsApp. Belum ada ongkir otomatis.</div>
                </div>
                <div class="mb-3" id="boxAlamat" style="display:none">
                    <label for="address" class="form-label">Alamat pengantaran</label>
                    <textarea id="address" name="address" rows="2" class="form-control @error('address') is-invalid @enderror" maxlength="255">{{ old('address') }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="payment_method" class="form-label">Rencana pembayaran</label>
                    <select id="payment_method" name="payment_method" class="form-select">
                        @foreach(\App\Models\Order::PAYMENT_METHODS as $k => $v)
                            <option value="{{ $k }}" {{ old('payment_method', 'tunai') === $k ? 'selected' : '' }}>{{ $v }}</option>
                        @endforeach
                    </select>
                    <div class="form-text">Pembayaran dilakukan langsung di warung (atau sesuai arahan warung), bukan lewat website ini.</div>
                </div>

                <div class="mb-3">
                    <label for="notes" class="form-label">Catatan (opsional)</label>
                    <textarea id="notes" name="notes" rows="2" class="form-control" maxlength="255" placeholder="Contoh: tidak pedas">{{ old('notes') }}</textarea>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input @error('consent') is-invalid @enderror" type="checkbox" name="consent" id="consent" value="1" {{ old('consent') ? 'checked' : '' }} required>
                    <label class="form-check-label small" for="consent">
                        Saya setuju data nama, nomor WhatsApp, dan alamat (jika diantar) dipakai {{ setting('store_name') }} hanya untuk memproses pesanan ini,
                        sesuai UU Pelindungan Data Pribadi. Saya dapat meminta data saya dihapus lewat WhatsApp warung.
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100" id="btnSubmit"><i class="bi bi-whatsapp"></i> Simpan pesanan dan lanjut ke WhatsApp</button>
            </form>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h2 class="h5">Ringkasan pesanan</h2>
                    <ul class="list-unstyled mb-3">
                        @foreach($lines as $line)
                            <li class="d-flex justify-content-between py-1 border-bottom">
                                <span>{{ $line['product']->name }} <span class="text-muted">x{{ $line['qty'] }}</span></span>
                                <span>{{ rupiah($line['line_total']) }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total</span><span class="fb-price">{{ rupiah($subtotal) }}</span></div>
                    <p class="small text-muted mt-2 mb-0">Total dihitung ulang oleh server dari harga terkini saat pesanan disimpan.</p>
                    <a href="{{ route('cart') }}" class="small">Ubah keranjang</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
    (function () {
        var antar = document.getElementById('f_antar'), ambil = document.getElementById('f_ambil'), box = document.getElementById('boxAlamat');
        function sync() { box.style.display = antar.checked ? '' : 'none'; }
        antar.addEventListener('change', sync); ambil.addEventListener('change', sync); sync();

        // Satu klik, satu pesanan: tombol dikunci setelah dikirim (kunci idempotensi ada di server).
        var form = document.getElementById('formCheckout'), btn = document.getElementById('btnSubmit');
        form.addEventListener('submit', function () {
            if (form.checkValidity()) { btn.disabled = true; btn.textContent = 'Menyimpan pesanan...'; }
        });
    })();
</script>
@endpush
