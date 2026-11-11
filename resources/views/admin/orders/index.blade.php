@extends('layouts.admin')

@section('title', 'Pesanan')

@section('content')
<form method="GET" class="ad-card p-3 mb-3">
    <div class="row g-2 align-items-end">
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="aktif" @selected(($filters['status'] ?? '') === 'aktif')>Aktif (menunggu + diproses)</option>
                @foreach(\App\Models\Order::STATUSES as $k => $v)
                    <option value="{{ $k }}" @selected(($filters['status'] ?? '') === $k)>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Kanal</label>
            <select name="channel" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="online" @selected(($filters['channel'] ?? '') === 'online')>Online</option>
                <option value="offline" @selected(($filters['channel'] ?? '') === 'offline')>Warung (offline)</option>
            </select>
        </div>
        <div class="col-6 col-md-2">
            <label class="form-label small mb-1">Pembayaran</label>
            <select name="payment" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="belum_bayar" @selected(($filters['payment'] ?? '') === 'belum_bayar')>Belum dibayar</option>
                <option value="lunas" @selected(($filters['payment'] ?? '') === 'lunas')>Lunas</option>
            </select>
        </div>
        <div class="col-6 col-md-2"><label class="form-label small mb-1">Dari</label><input type="date" name="dari" value="{{ $filters['dari'] ?? '' }}" class="form-control form-control-sm"></div>
        <div class="col-6 col-md-2"><label class="form-label small mb-1">Sampai</label><input type="date" name="sampai" value="{{ $filters['sampai'] ?? '' }}" class="form-control form-control-sm"></div>
        <div class="col-6 col-md-2"><label class="form-label small mb-1">Cari</label><input type="search" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="Kode/nama/HP" class="form-control form-control-sm"></div>
    </div>
    <div class="mt-2 d-flex gap-2">
        <button class="btn btn-primary btn-sm">Terapkan</button>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
        <a href="{{ route('admin.pos') }}" class="btn btn-outline-dark btn-sm ms-auto"><i class="bi bi-calculator"></i> Input di warung</a>
    </div>
</form>

<div class="ad-card p-3">
    <div class="table-responsive">
        <table class="table table-hover table-nowrap align-middle mb-0">
            <thead><tr><th>Kode</th><th>Waktu</th><th>Kanal</th><th>Pelanggan</th><th>Item</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($orders as $o)
                <tr class="{{ $o->status === 'menunggu' ? 'table-warning' : '' }}">
                    <td><a href="{{ route('admin.orders.show', $o) }}" class="fw-semibold">{{ $o->code }}</a></td>
                    <td>{{ $o->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $o->channel === 'online' ? 'Online' : 'Warung' }}</td>
                    <td>{{ $o->customer_name ?: '-' }}@if($o->customer_phone)<div class="small text-muted">{{ $o->customer_phone }}</div>@endif</td>
                    <td>{{ $o->items->sum('quantity') }} porsi</td>
                    <td class="text-end">{{ rupiah($o->total) }}</td>
                    <td>@include('partials.order-badges', ['order' => $o])</td>
                    <td class="text-end"><a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-outline-primary">Detail</a></td>
                </tr>
            @empty
                <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada pesanan yang cocok.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection

@push('scripts')
<script>
    // Halaman ini sudah menampilkan pesanan terbaru, jadi tandai sudah dilihat dan segarkan otomatis.
    window.fbAckOrders({{ $latestId }});
    setTimeout(function () { if (!document.querySelector('.modal.show')) location.reload(); }, 60000);
</script>
@endpush
