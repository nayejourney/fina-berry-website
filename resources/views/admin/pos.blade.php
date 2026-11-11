@extends('layouts.admin')

@section('title', 'Input pesanan di warung')

@section('content')
<form method="POST" action="{{ route('admin.pos.store') }}" id="formPos">
    @csrf
    <div class="row g-3">
        <div class="col-lg-8">
            <input type="search" id="cari" class="form-control form-control-lg mb-3" placeholder="Cari menu..." autocomplete="off">
            @foreach($categories as $cat)
                <h2 class="h6 text-uppercase text-muted mt-3">{{ $cat->name }}</h2>
                <div class="row g-2">
                    @foreach($cat->products as $p)
                        <div class="col-12 col-md-6 pos-col" data-name="{{ strtolower($p->name) }}">
                            <div class="pos-item d-flex align-items-center gap-2" data-price="{{ $p->price }}">
                                <div class="me-auto">
                                    <div class="fw-semibold">{{ $p->name }}</div>
                                    <div class="small text-muted">{{ rupiah($p->price) }}</div>
                                </div>
                                <button type="button" class="btn btn-outline-secondary pos-btn" data-d="-1" aria-label="Kurangi {{ $p->name }}">−</button>
                                <input type="number" name="items[{{ $p->id }}]" value="{{ old('items.'.$p->id, 0) }}" min="0" max="50" class="form-control pos-qty" inputmode="numeric" aria-label="Jumlah {{ $p->name }}">
                                <button type="button" class="btn btn-primary pos-btn" data-d="1" aria-label="Tambah {{ $p->name }}">+</button>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <div class="col-lg-4">
            <div class="ad-card p-3 position-sticky" style="top: 12px;">
                <h2 class="h6">Ringkasan</h2>
                <div id="ringkas" class="small mb-2 text-muted">Belum ada menu dipilih.</div>
                <div class="d-flex justify-content-between fs-4 fw-bold mb-3"><span>Total</span><span id="total">Rp 0</span></div>

                <label class="form-label small mb-1">Metode bayar</label>
                <select name="payment_method" class="form-select mb-2">
                    @foreach(\App\Models\Order::PAYMENT_METHODS as $k => $v)<option value="{{ $k }}" @selected(old('payment_method', 'tunai') === $k)>{{ $v }}</option>@endforeach
                </select>
                <label class="form-label small mb-1">Layanan</label>
                <select name="fulfillment" class="form-select mb-2">
                    <option value="makan_ditempat" @selected(old('fulfillment') !== 'ambil')>Makan di tempat</option>
                    <option value="ambil" @selected(old('fulfillment') === 'ambil')>Bungkus / ambil</option>
                </select>
                <label class="form-label small mb-1">Nama / nomor meja (opsional)</label>
                <input type="text" name="customer_name" value="{{ old('customer_name') }}" class="form-control mb-2" maxlength="80">
                <label class="form-label small mb-1">Catatan (opsional)</label>
                <input type="text" name="notes" value="{{ old('notes') }}" class="form-control mb-3" maxlength="255">

                <button class="btn btn-primary btn-lg w-100" id="btnSimpan"><i class="bi bi-check2-circle"></i> Simpan sebagai selesai dan lunas</button>
                <button type="button" class="btn btn-link w-100" id="btnReset">Kosongkan</button>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
    (function () {
        var rows = document.querySelectorAll('.pos-item');
        var rp = function (n) { return 'Rp ' + n.toLocaleString('id-ID'); };

        function hitung() {
            var total = 0, daftar = [];
            rows.forEach(function (row) {
                var input = row.querySelector('input');
                var qty = Math.max(0, Math.min(50, parseInt(input.value, 10) || 0));
                row.classList.toggle('active', qty > 0);
                if (qty > 0) {
                    total += qty * parseInt(row.dataset.price, 10);
                    daftar.push(qty + ' x ' + row.querySelector('.fw-semibold').textContent);
                }
            });
            document.getElementById('total').textContent = rp(total);
            document.getElementById('ringkas').textContent = daftar.length ? daftar.join(', ') : 'Belum ada menu dipilih.';
        }

        rows.forEach(function (row) {
            var input = row.querySelector('input');
            row.querySelectorAll('button').forEach(function (b) {
                b.addEventListener('click', function () {
                    input.value = Math.max(0, Math.min(50, (parseInt(input.value, 10) || 0) + parseInt(b.dataset.d, 10)));
                    hitung();
                });
            });
            input.addEventListener('input', hitung);
        });

        document.getElementById('btnReset').addEventListener('click', function () {
            rows.forEach(function (row) { row.querySelector('input').value = 0; }); hitung();
        });

        document.getElementById('cari').addEventListener('input', function (e) {
            var q = e.target.value.toLowerCase().trim();
            document.querySelectorAll('.pos-col').forEach(function (c) { c.style.display = c.dataset.name.indexOf(q) > -1 ? '' : 'none'; });
        });

        document.getElementById('formPos').addEventListener('submit', function (e) {
            var btn = document.getElementById('btnSimpan');
            btn.disabled = true; btn.textContent = 'Menyimpan...';
        });
        hitung();
    })();
</script>
@endpush
