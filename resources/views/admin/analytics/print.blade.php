@extends('layouts.print')

@section('title', 'Laporan Laba Rugi dan Analitik '.$f['from']->format('d-m-Y').' sampai '.$f['to']->format('d-m-Y'))

@section('content')
<div class="print-head">
    <h1 style="margin:0;font-size:20px">Laporan Laba Rugi dan Analitik</h1>
    <div>{{ setting('store_name') }}</div>
    <div style="font-size:12px;color:#555">Periode {{ $f['from']->translatedFormat('d F Y') }} sampai {{ $f['to']->translatedFormat('d F Y') }} &middot; dicetak {{ now()->translatedFormat('d F Y H:i') }} oleh {{ auth()->user()->name }}</div>
</div>

<div class="print-kpi">
    <div><small>Pendapatan</small><strong>{{ rupiah($f['revenue']) }}</strong></div>
    <div><small>Laba kotor (margin {{ $f['gross_margin'] }}%)</small><strong>{{ rupiah($f['gross_profit']) }}</strong></div>
    <div><small>{{ $f['net_profit'] >= 0 ? 'Laba bersih' : 'Rugi bersih' }} (margin {{ $f['net_margin'] }}%)</small><strong>{{ rupiah($f['net_profit']) }}</strong></div>
    <div><small>Pesanan selesai</small><strong>{{ $f['sales']['order_count'] }}</strong></div>
</div>

<h2 class="print-h2">Laporan laba rugi</h2>
<table class="print-table">
    <tr><td>Pendapatan (pesanan selesai dan lunas)</td><td class="num">{{ rupiah($f['revenue']) }}</td></tr>
    <tr><td>Harga pokok penjualan (HPP)</td><td class="num">- {{ rupiah($f['cogs']) }}</td></tr>
    <tr><th>Laba kotor</th><th class="num">{{ rupiah($f['gross_profit']) }}</th></tr>
    @foreach($f['expense_by_category'] as $k => $v)
        <tr><td>&nbsp;&nbsp;{{ \App\Models\Expense::CATEGORIES[$k] ?? $k }}</td><td class="num">- {{ rupiah($v) }}</td></tr>
    @endforeach
    <tr><td>Total biaya operasional</td><td class="num">- {{ rupiah($f['opex']) }}</td></tr>
    <tr><th>{{ $f['net_profit'] >= 0 ? 'Laba bersih' : 'Rugi bersih' }}</th><th class="num">{{ rupiah($f['net_profit']) }}</th></tr>
    <tr><td>Margin kotor / margin bersih</td><td class="num">{{ $f['gross_margin'] }}% / {{ $f['net_margin'] }}%</td></tr>
    <tr><td>Tingkat pembatalan pesanan</td><td class="num">{{ $f['cancel_rate'] }}%</td></tr>
</table>

<h2 class="print-h2">Tren harian</h2>
<canvas id="chTren" width="700" height="240" style="max-width:100%"></canvas>

<h2 class="print-h2">Rincian harian</h2>
<table class="print-table"><tr><th>Tanggal</th><th class="num">Pendapatan</th><th class="num">HPP</th><th class="num">Biaya ops.</th><th class="num">Laba bersih</th></tr>
    @foreach($f['daily'] as $d)<tr><td>{{ $d['date']->format('d/m/Y') }}</td><td class="num">{{ rupiah($d['revenue']) }}</td><td class="num">{{ rupiah($d['cogs']) }}</td><td class="num">{{ rupiah($d['opex']) }}</td><td class="num">{{ rupiah($d['net']) }}</td></tr>@endforeach
</table>

<h2 class="print-h2">Kontribusi laba per menu</h2>
<table class="print-table"><tr><th>Menu</th><th class="num">Porsi</th><th class="num">Pendapatan</th><th class="num">Laba</th><th class="num">Margin</th></tr>
    @foreach($f['menu_profit'] as $m)<tr><td>{{ $m['name'] }}</td><td class="num">{{ $m['qty'] }}</td><td class="num">{{ rupiah($m['revenue']) }}</td><td class="num">{{ rupiah($m['profit']) }}</td><td class="num">{{ $m['margin'] }}%</td></tr>@endforeach
</table>

<h2 class="print-h2">Jam dan hari ramai (jumlah pesanan)</h2>
<table class="print-table">
    <tr><th>Hari</th>@foreach(['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $h)<th class="num">{{ $h }}</th>@endforeach</tr>
    <tr><td>Pesanan</td>@foreach($f['by_weekday'] as $v)<td class="num">{{ $v }}</td>@endforeach</tr>
</table>
<table class="print-table">
    <tr><th>Jam</th>@foreach($f['by_hour'] as $jam => $v)@if($v > 0)<th class="num">{{ str_pad($jam, 2, '0', STR_PAD_LEFT) }}</th>@endif @endforeach</tr>
    <tr><td>Pesanan</td>@foreach($f['by_hour'] as $jam => $v)@if($v > 0)<td class="num">{{ $v }}</td>@endif @endforeach</tr>
</table>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (function () {
        var daily = @json(collect($f['daily'])->map(fn ($d) => ['t' => $d['date']->format('d/m'), 'rev' => $d['revenue'], 'net' => $d['net']])->values());
        new Chart(document.getElementById('chTren'), {
            type: 'line',
            data: { labels: daily.map(function (d) { return d.t; }), datasets: [
                { label: 'Pendapatan', data: daily.map(function (d) { return d.rev; }), borderColor: '#a3173f', tension: .25 },
                { label: 'Laba bersih', data: daily.map(function (d) { return d.net; }), borderColor: '#198754', tension: .25 }
            ]},
            options: { responsive: false, animation: false }
        });
    })();
</script>
@endpush
