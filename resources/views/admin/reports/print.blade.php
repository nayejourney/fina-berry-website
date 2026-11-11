@extends('layouts.print')

@section('title', 'Laporan Penjualan '.$r['from']->format('d-m-Y').' sampai '.$r['to']->format('d-m-Y'))

@section('content')
<div class="print-head">
    <h1 style="margin:0;font-size:20px">Laporan Penjualan</h1>
    <div>{{ setting('store_name') }}</div>
    <div style="font-size:12px;color:#555">Periode {{ $r['from']->translatedFormat('d F Y') }} sampai {{ $r['to']->translatedFormat('d F Y') }} &middot; dicetak {{ now()->translatedFormat('d F Y H:i') }} oleh {{ auth()->user()->name }}</div>
</div>

<div class="print-kpi">
    <div><small>Pendapatan</small><strong>{{ rupiah($r['revenue']) }}</strong></div>
    <div><small>Pesanan selesai</small><strong>{{ $r['order_count'] }}</strong></div>
    <div><small>Rata-rata per pesanan</small><strong>{{ rupiah($r['avg_order']) }}</strong></div>
    <div><small>Porsi terjual</small><strong>{{ $r['items_sold'] }}</strong></div>
    <div><small>Pesanan dibatalkan</small><strong>{{ $r['cancelled_count'] }}</strong></div>
</div>
<p style="font-size:11px;color:#555">Pendapatan = pesanan berstatus Selesai dengan pembayaran tervalidasi, semua kanal.</p>

<h2 class="print-h2">Per kanal</h2>
<table class="print-table"><tr><th>Kanal</th><th class="num">Pesanan</th><th class="num">Pendapatan</th></tr>
    @foreach($r['by_channel'] as $c)<tr><td>{{ $c['label'] }}</td><td class="num">{{ $c['orders'] }}</td><td class="num">{{ rupiah($c['revenue']) }}</td></tr>@endforeach
</table>

<h2 class="print-h2">Per metode pembayaran</h2>
<table class="print-table"><tr><th>Metode</th><th class="num">Pesanan</th><th class="num">Pendapatan</th></tr>
    @foreach($r['by_method'] as $c)<tr><td>{{ $c['label'] }}</td><td class="num">{{ $c['orders'] }}</td><td class="num">{{ rupiah($c['revenue']) }}</td></tr>@endforeach
</table>

<h2 class="print-h2">Per menu</h2>
<table class="print-table"><tr><th>Menu</th><th class="num">Porsi</th><th class="num">Pendapatan</th></tr>
    @foreach($r['by_menu'] as $m)<tr><td>{{ $m['name'] }}</td><td class="num">{{ $m['qty'] }}</td><td class="num">{{ rupiah($m['revenue']) }}</td></tr>@endforeach
</table>

<h2 class="print-h2">Rekap per hari</h2>
<table class="print-table"><tr><th>Tanggal</th><th class="num">Pesanan</th><th class="num">Pendapatan</th></tr>
    @foreach($r['by_day'] as $d)<tr><td>{{ $d['date']->translatedFormat('D, d M Y') }}</td><td class="num">{{ $d['orders'] }}</td><td class="num">{{ rupiah($d['revenue']) }}</td></tr>@endforeach
</table>

@if($withTransactions)
<h2 class="print-h2">Riwayat transaksi ({{ $r['orders']->count() }})</h2>
<table class="print-table"><tr><th>Kode</th><th>Waktu</th><th>Kanal</th><th>Bayar</th><th class="num">Total</th></tr>
    @foreach($r['orders'] as $o)<tr><td>{{ $o->code }}</td><td>{{ $o->created_at->format('d/m/Y H:i') }}</td><td>{{ $o->channel === 'online' ? 'Online' : 'Warung' }}</td><td>{{ $o->payment_method_label }}</td><td class="num">{{ rupiah($o->total) }}</td></tr>@endforeach
</table>
@endif
@endsection
