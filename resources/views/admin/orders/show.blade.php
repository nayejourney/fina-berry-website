@extends('layouts.admin')

@section('title', 'Pesanan '.$order->code)

@section('content')
@php
    $u = auth()->user();
    $aktif = in_array($order->status, ['menunggu', 'diproses'], true);
@endphp
<div class="row g-3">
    <div class="col-lg-8">
        <div class="ad-card p-3 p-md-4 mb-3">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                <div>
                    <h2 class="h4 mb-1">{{ $order->code }}</h2>
                    <div class="text-muted small">{{ $order->created_at->translatedFormat('l, d F Y H:i') }} &middot; {{ $order->channel === 'online' ? 'Online (website/WhatsApp)' : 'Input di warung' }}</div>
                </div>
                <div>@include('partials.order-badges', ['order' => $order])</div>
            </div>

            <dl class="row small mb-3">
                <dt class="col-sm-4">Pelanggan</dt><dd class="col-sm-8">{{ $order->customer_name ?: '-' }}</dd>
                @if($order->customer_phone)
                    <dt class="col-sm-4">WhatsApp</dt>
                    <dd class="col-sm-8">{{ $order->customer_phone }}
                        @if($waCustomer)<a href="{{ $waCustomer }}" target="_blank" rel="noopener" class="btn btn-success btn-sm ms-2 py-0"><i class="bi bi-whatsapp"></i> Hubungi</a>@endif
                    </dd>
                @endif
                <dt class="col-sm-4">Pengambilan</dt><dd class="col-sm-8">{{ $order->fulfillment_label }}@if($order->address): {{ $order->address }}@endif</dd>
                <dt class="col-sm-4">Metode bayar</dt><dd class="col-sm-8">{{ $order->payment_method_label }}</dd>
                @if($order->notes)<dt class="col-sm-4">Catatan</dt><dd class="col-sm-8">{{ $order->notes }}</dd>@endif
                @if($order->cancel_reason)<dt class="col-sm-4">Alasan batal</dt><dd class="col-sm-8 text-danger">{{ $order->cancel_reason }}</dd>@endif
                @if($order->is_paid)
                    <dt class="col-sm-4">Pembayaran</dt>
                    <dd class="col-sm-8">Divalidasi {{ $order->paid_at?->format('d/m/Y H:i') }}@if($order->paymentValidator) oleh {{ $order->paymentValidator->name }}@endif</dd>
                @endif
                @if($order->consent_at)<dt class="col-sm-4">Persetujuan privasi</dt><dd class="col-sm-8">{{ $order->consent_at->format('d/m/Y H:i') }}</dd>@endif
            </dl>

            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                    <thead><tr><th>Menu</th><th class="text-end">Harga</th><th class="text-end">Jumlah</th><th class="text-end">Subtotal</th></tr></thead>
                    <tbody>
                    @foreach($order->items as $i)
                        <tr><td>{{ $i->product_name }}</td><td class="text-end">{{ rupiah($i->unit_price) }}</td><td class="text-end">{{ $i->quantity }}</td><td class="text-end">{{ rupiah($i->line_total) }}</td></tr>
                    @endforeach
                    </tbody>
                    <tfoot><tr><th colspan="3" class="text-end">Total</th><th class="text-end fs-5">{{ rupiah($order->total) }}</th></tr></tfoot>
                </table>
            </div>
        </div>

        <div class="ad-card p-3">
            <h3 class="h6">Riwayat status</h3>
            <ul class="status-timeline small mb-0">
                @foreach($order->statusLogs as $log)
                    <li>
                        <strong>{{ $log->from_status ? (\App\Models\Order::STATUSES[$log->from_status] ?? $log->from_status).' → ' : '' }}{{ \App\Models\Order::STATUSES[$log->to_status] ?? $log->to_status }}</strong>
                        <span class="text-muted">· {{ $log->created_at->format('d/m/Y H:i') }}{{ $log->user ? ' · '.$log->user->name : '' }}</span>
                        @if($log->reason)<div class="text-muted">{{ $log->reason }}</div>@endif
                    </li>
                @endforeach
            </ul>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="ad-card p-3 mb-3">
            <h3 class="h6">Tindakan</h3>
            <div class="d-grid gap-2">
                @if($order->status === 'menunggu')
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}">@csrf
                        <input type="hidden" name="to" value="diproses">
                        <button class="btn btn-info w-100"><i class="bi bi-play-circle"></i> Proses pesanan</button>
                    </form>
                @endif

                @if(! $order->is_paid && $order->status !== 'dibatalkan')
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#mdlBayar"><i class="bi bi-cash-coin"></i> Validasi pembayaran</button>
                @endif

                @if($order->status === 'diproses')
                    <form method="POST" action="{{ route('admin.orders.status', $order) }}">@csrf
                        <input type="hidden" name="to" value="selesai">
                        <button class="btn btn-primary w-100" @disabled(! $order->is_paid)><i class="bi bi-check2-circle"></i> Tandai selesai</button>
                    </form>
                    @unless($order->is_paid)<div class="small text-danger">Validasi pembayaran dulu sebelum menandai selesai.</div>@endunless
                @endif

                @if($aktif)
                    <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#mdlBatal"><i class="bi bi-x-circle"></i> Batalkan pesanan</button>
                @endif

                @if($order->status === 'selesai' && $u->isOwnerOrAbove())
                    <button class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#mdlKoreksi"><i class="bi bi-pencil-square"></i> Koreksi pesanan selesai</button>
                @endif

                @if($order->is_paid && $order->status !== 'selesai' && $u->isOwnerOrAbove())
                    <button class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#mdlBatalBayar">Batalkan validasi pembayaran</button>
                @endif

                <a href="{{ route('admin.orders.receipt', $order) }}" target="_blank" class="btn btn-outline-dark"><i class="bi bi-printer"></i> Cetak struk</a>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-link">&larr; Kembali ke daftar</a>
            </div>
        </div>
    </div>
</div>

{{-- Validasi pembayaran --}}
<div class="modal fade" id="mdlBayar" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('admin.orders.pay', $order) }}">@csrf
    <div class="modal-header"><h5 class="modal-title">Validasi pembayaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <div class="modal-body">
        <p>Konfirmasi uang sebesar <strong>{{ rupiah($order->total) }}</strong> sudah diterima (pembayaran offline).</p>
        <label class="form-label">Metode pembayaran</label>
        <select name="payment_method" class="form-select mb-3">
            @foreach(\App\Models\Order::PAYMENT_METHODS as $k => $v)<option value="{{ $k }}" @selected($order->payment_method === $k)>{{ $v }}</option>@endforeach
        </select>
        <label class="form-label">Catatan (opsional)</label>
        <input type="text" name="note" class="form-control" maxlength="200" placeholder="Contoh: transfer BRI a.n. ...">
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-success">Validasi lunas</button></div>
</form></div></div>

{{-- Pembatalan --}}
<div class="modal fade" id="mdlBatal" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('admin.orders.status', $order) }}">@csrf
    <input type="hidden" name="to" value="dibatalkan">
    <div class="modal-header"><h5 class="modal-title">Batalkan pesanan</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <div class="modal-body">
        <label class="form-label">Alasan pembatalan (wajib)</label>
        <textarea name="reason" class="form-control" rows="3" minlength="5" maxlength="200" required placeholder="Contoh: pelanggan tidak merespons WhatsApp"></textarea>
        <div class="form-text">Pesanan yang dibatalkan tidak dihitung sebagai pendapatan.</div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Kembali</button><button class="btn btn-danger">Batalkan pesanan</button></div>
</form></div></div>

@if($u->isOwnerOrAbove())
{{-- Koreksi pesanan Selesai (khusus Pemilik) --}}
<div class="modal fade" id="mdlKoreksi" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('admin.orders.status', $order) }}">@csrf
    <div class="modal-header"><h5 class="modal-title">Koreksi pesanan selesai</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <div class="modal-body">
        <label class="form-label">Ubah menjadi</label>
        <select name="to" class="form-select mb-3"><option value="diproses">Diproses (dibuka kembali)</option><option value="dibatalkan">Dibatalkan</option></select>
        <label class="form-label">Alasan koreksi (wajib)</label>
        <textarea name="reason" class="form-control" rows="3" minlength="5" maxlength="200" required></textarea>
        <div class="form-text">Koreksi tercatat di riwayat status dan log aktivitas.</div>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-warning">Simpan koreksi</button></div>
</form></div></div>

{{-- Batalkan validasi pembayaran --}}
<div class="modal fade" id="mdlBatalBayar" tabindex="-1" aria-hidden="true"><div class="modal-dialog"><form class="modal-content" method="POST" action="{{ route('admin.orders.pay.revert', $order) }}">@csrf
    <div class="modal-header"><h5 class="modal-title">Batalkan validasi pembayaran</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
    <div class="modal-body">
        <label class="form-label">Alasan (wajib)</label>
        <textarea name="reason" class="form-control" rows="3" minlength="5" maxlength="200" required></textarea>
    </div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button><button class="btn btn-secondary">Batalkan validasi</button></div>
</form></div></div>
@endif
@endsection
