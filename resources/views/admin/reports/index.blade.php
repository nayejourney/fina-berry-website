@extends('layouts.admin')

@section('title', 'Laporan penjualan')

@section('content')
@include('partials.period-filter', ['route' => 'admin.reports.index'])
@php $q = ['dari' => $from->toDateString(), 'sampai' => $to->toDateString()]; @endphp

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('admin.reports.print', $q) }}" target="_blank" class="btn btn-dark btn-sm"><i class="bi bi-printer"></i> Cetak laporan</a>
    <a href="{{ route('admin.reports.export', $q + ['tipe' => 'ringkasan']) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-filetype-csv"></i> Unduh ringkasan (CSV)</a>
    <a href="{{ route('admin.reports.export', $q + ['tipe' => 'transaksi']) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-filetype-csv"></i> Unduh riwayat transaksi (CSV)</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-6 col-xl-3"><div class="ad-card p-3"><div class="text-muted small">Pendapatan</div><div class="ad-stat">{{ rupiah($r['revenue']) }}</div></div></div>
    <div class="col-6 col-xl-3"><div class="ad-card p-3"><div class="text-muted small">Pesanan selesai</div><div class="ad-stat">{{ $r['order_count'] }}</div></div></div>
    <div class="col-6 col-xl-3"><div class="ad-card p-3"><div class="text-muted small">Rata-rata per pesanan</div><div class="ad-stat">{{ rupiah($r['avg_order']) }}</div></div></div>
    <div class="col-6 col-xl-3"><div class="ad-card p-3"><div class="text-muted small">Porsi terjual / dibatalkan</div><div class="ad-stat">{{ $r['items_sold'] }} <small class="text-muted fs-6">/ {{ $r['cancelled_count'] }}</small></div></div></div>
</div>
<p class="small text-muted">Hanya pesanan berstatus <strong>Selesai</strong> dengan pembayaran <strong>tervalidasi</strong> yang dihitung sebagai pendapatan, dari semua kanal.</p>

<div class="row g-3 mb-3">
    <div class="col-lg-6">
        <div class="ad-card p-3 h-100"><h2 class="h6">Per kanal</h2>
            <table class="table table-sm mb-0"><thead><tr><th>Kanal</th><th class="text-end">Pesanan</th><th class="text-end">Pendapatan</th></tr></thead><tbody>
                @foreach($r['by_channel'] as $c)<tr><td>{{ $c['label'] }}</td><td class="text-end">{{ $c['orders'] }}</td><td class="text-end">{{ rupiah($c['revenue']) }}</td></tr>@endforeach
            </tbody></table>
            <h2 class="h6 mt-3">Per metode pembayaran</h2>
            <table class="table table-sm mb-0"><thead><tr><th>Metode</th><th class="text-end">Pesanan</th><th class="text-end">Pendapatan</th></tr></thead><tbody>
                @foreach($r['by_method'] as $c)<tr><td>{{ $c['label'] }}</td><td class="text-end">{{ $c['orders'] }}</td><td class="text-end">{{ rupiah($c['revenue']) }}</td></tr>@endforeach
            </tbody></table>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="ad-card p-3 h-100"><h2 class="h6">Per menu</h2>
            <div class="table-responsive" style="max-height: 360px; overflow:auto">
            <table class="table table-sm mb-0"><thead><tr><th>Menu</th><th class="text-end">Porsi</th><th class="text-end">Pendapatan</th></tr></thead><tbody>
                @forelse($r['by_menu'] as $m)<tr><td>{{ $m['name'] }}</td><td class="text-end">{{ $m['qty'] }}</td><td class="text-end">{{ rupiah($m['revenue']) }}</td></tr>
                @empty<tr><td colspan="3" class="text-center text-muted">Belum ada data.</td></tr>@endforelse
            </tbody></table></div>
        </div>
    </div>
</div>

<div class="ad-card p-3 mb-3"><h2 class="h6">Rekap per hari</h2>
    <div class="table-responsive" style="max-height: 360px; overflow:auto">
    <table class="table table-sm mb-0"><thead><tr><th>Tanggal</th><th class="text-end">Pesanan</th><th class="text-end">Pendapatan</th></tr></thead><tbody>
        @foreach(array_reverse($r['by_day']) as $d)<tr><td>{{ $d['date']->translatedFormat('D, d M Y') }}</td><td class="text-end">{{ $d['orders'] }}</td><td class="text-end">{{ rupiah($d['revenue']) }}</td></tr>@endforeach
    </tbody></table></div>
</div>

<div class="ad-card p-3"><h2 class="h6">Riwayat transaksi</h2>
    <div class="table-responsive" style="max-height: 420px; overflow:auto">
    <table class="table table-sm table-nowrap mb-0"><thead><tr><th>Kode</th><th>Waktu</th><th>Kanal</th><th>Bayar</th><th class="text-end">Total</th></tr></thead><tbody>
        @forelse($r['orders']->sortByDesc('created_at')->take(100) as $o)
            <tr><td><a href="{{ route('admin.orders.show', $o) }}">{{ $o->code }}</a></td><td>{{ $o->created_at->format('d/m/Y H:i') }}</td><td>{{ $o->channel === 'online' ? 'Online' : 'Warung' }}</td><td>{{ $o->payment_method_label }}</td><td class="text-end">{{ rupiah($o->total) }}</td></tr>
        @empty<tr><td colspan="5" class="text-center text-muted">Belum ada transaksi.</td></tr>@endforelse
    </tbody></table></div>
    @if($r['orders']->count() > 100)<div class="small text-muted mt-1">Menampilkan 100 transaksi terbaru. Gunakan cetak atau CSV untuk seluruh {{ $r['orders']->count() }} transaksi.</div>@endif
</div>
@endsection
