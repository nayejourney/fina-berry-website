@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-6 col-xl-3"><div class="ad-card p-3"><div class="text-muted small">Pesanan hari ini</div><div class="ad-stat">{{ $ordersToday }}</div></div></div>
    <div class="col-6 col-xl-3"><a href="{{ route('admin.orders.index', ['status' => 'menunggu']) }}" class="text-decoration-none text-dark"><div class="ad-card p-3"><div class="text-muted small">Menunggu</div><div class="ad-stat text-warning">{{ $waiting }}</div></div></a></div>
    <div class="col-6 col-xl-3"><a href="{{ route('admin.orders.index', ['status' => 'diproses']) }}" class="text-decoration-none text-dark"><div class="ad-card p-3"><div class="text-muted small">Diproses</div><div class="ad-stat text-info">{{ $processing }}</div></div></a></div>
    <div class="col-6 col-xl-3"><div class="ad-card p-3"><div class="text-muted small">Belum divalidasi bayar</div><div class="ad-stat text-danger">{{ $unpaid }}</div></div></div>
    @isset($revenueToday)
        <div class="col-6 col-xl-3"><div class="ad-card p-3"><div class="text-muted small">Pendapatan hari ini</div><div class="ad-stat">{{ rupiah($revenueToday) }}</div><div class="small text-muted">{{ $doneToday }} pesanan selesai</div></div></div>
    @endisset
    <div class="col-6 col-xl-3"><a href="{{ route('admin.products.index') }}" class="text-decoration-none text-dark"><div class="ad-card p-3"><div class="text-muted small">Menu ditandai habis</div><div class="ad-stat">{{ $soldOut }}</div></div></a></div>
</div>

<div class="d-flex flex-wrap gap-2 mb-4">
    <a href="{{ route('admin.pos') }}" class="btn btn-primary btn-lg"><i class="bi bi-calculator"></i> Input pesanan di warung</a>
    <a href="{{ route('admin.orders.index', ['status' => 'aktif']) }}" class="btn btn-outline-dark btn-lg"><i class="bi bi-receipt"></i> Pesanan aktif</a>
</div>

<div class="ad-card p-3">
    <h2 class="h6 mb-3">Pesanan terbaru</h2>
    <div class="table-responsive">
        <table class="table table-sm table-nowrap align-middle mb-0">
            <thead><tr><th>Kode</th><th>Waktu</th><th>Kanal</th><th>Pelanggan</th><th class="text-end">Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            @forelse($recent as $o)
                <tr>
                    <td><a href="{{ route('admin.orders.show', $o) }}">{{ $o->code }}</a></td>
                    <td>{{ $o->created_at->format('d/m H:i') }}</td>
                    <td>{{ $o->channel === 'online' ? 'Online' : 'Warung' }}</td>
                    <td>{{ $o->customer_name ?: '-' }}</td>
                    <td class="text-end">{{ rupiah($o->total) }}</td>
                    <td>@include('partials.order-badges', ['order' => $o])</td>
                    <td class="text-end"><a href="{{ route('admin.orders.show', $o) }}" class="btn btn-sm btn-outline-primary">Buka</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">Belum ada pesanan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
