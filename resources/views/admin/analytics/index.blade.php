@extends('layouts.admin')

@section('title', 'Laba rugi dan analitik')

@section('content')
@include('partials.period-filter', ['route' => 'admin.analytics.index', 'from' => $f['from'], 'to' => $f['to']])
@php
    $q = ['dari' => $f['from']->toDateString(), 'sampai' => $f['to']->toDateString()];
    $rev = $f['sales']['revenue'];
    $growth = function ($v) {
        if ($v === null) return '<span class="text-muted">tanpa pembanding</span>';
        return '<span class="'.($v >= 0 ? 'text-success' : 'text-danger').'">'.($v >= 0 ? '▲ ' : '▼ ').abs($v).'%</span> vs periode sebelumnya';
    };
@endphp

<div class="d-flex flex-wrap gap-2 mb-3">
    <a href="{{ route('admin.analytics.print', $q) }}" target="_blank" class="btn btn-dark btn-sm"><i class="bi bi-printer"></i> Cetak laporan analitik</a>
    <a href="{{ route('admin.analytics.export', $q) }}" class="btn btn-outline-dark btn-sm"><i class="bi bi-filetype-csv"></i> Unduh (CSV)</a>
</div>

@if($f['cogs'] === 0 && $rev > 0)
    <div class="alert alert-warning">HPP menu belum terisi (bernilai 0), sehingga laba tampak sama dengan pendapatan. Isi HPP per menu di halaman <a href="{{ route('admin.products.index') }}">Menu</a>.</div>
@endif

<div class="row g-3 mb-3">
    <div class="col-6 col-xl"><div class="ad-card p-3 h-100"><div class="text-muted small">Pendapatan</div><div class="ad-stat">{{ rupiah($rev) }}</div><div class="small">{!! $growth($f['revenue_growth']) !!}</div></div></div>
    <div class="col-6 col-xl"><div class="ad-card p-3 h-100"><div class="text-muted small">HPP</div><div class="ad-stat">{{ rupiah($f['cogs']) }}</div></div></div>
    <div class="col-6 col-xl"><div class="ad-card p-3 h-100"><div class="text-muted small">Laba kotor</div><div class="ad-stat">{{ rupiah($f['gross_profit']) }}</div><div class="small text-muted">Margin {{ $f['gross_margin'] }}%</div></div></div>
    <div class="col-6 col-xl"><div class="ad-card p-3 h-100"><div class="text-muted small">Biaya operasional</div><div class="ad-stat">{{ rupiah($f['opex']) }}</div></div></div>
    <div class="col-12 col-xl"><div class="ad-card p-3 h-100 border-start border-4 {{ $f['net_profit'] >= 0 ? 'border-success' : 'border-danger' }}"><div class="text-muted small">{{ $f['net_profit'] >= 0 ? 'Laba bersih' : 'Rugi bersih' }}</div><div class="ad-stat {{ $f['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">{{ rupiah($f['net_profit']) }}</div><div class="small text-muted">Margin {{ $f['net_margin'] }}%</div><div class="small">{!! $growth($f['net_growth']) !!}</div></div></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-5">
        <div class="ad-card p-3 h-100">
            <h2 class="h6">Laporan laba rugi</h2>
            <table class="table table-sm mb-0">
                <tr><td>Pendapatan (selesai dan lunas)</td><td class="text-end">{{ rupiah($rev) }}</td></tr>
                <tr><td>Harga pokok penjualan (HPP)</td><td class="text-end">− {{ rupiah($f['cogs']) }}</td></tr>
                <tr class="fw-bold"><td>Laba kotor <small class="text-muted">({{ $f['gross_margin'] }}%)</small></td><td class="text-end">{{ rupiah($f['gross_profit']) }}</td></tr>
                @foreach($f['expense_by_category'] as $k => $v)
                    <tr><td class="ps-4 small">{{ \App\Models\Expense::CATEGORIES[$k] ?? $k }}</td><td class="text-end small">− {{ rupiah($v) }}</td></tr>
                @endforeach
                <tr><td>Total biaya operasional</td><td class="text-end">− {{ rupiah($f['opex']) }}</td></tr>
                <tr class="fw-bold table-light"><td>{{ $f['net_profit'] >= 0 ? 'Laba bersih' : 'Rugi bersih' }} <small class="text-muted">({{ $f['net_margin'] }}%)</small></td><td class="text-end {{ $f['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">{{ rupiah($f['net_profit']) }}</td></tr>
            </table>
            <div class="small text-muted mt-2">Pembanding ({{ $f['prev']['from']->format('d/m') }}-{{ $f['prev']['to']->format('d/m/Y') }}): pendapatan {{ rupiah($f['prev']['revenue']) }}, laba bersih {{ rupiah($f['prev']['net']) }}.</div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="ad-card p-3 h-100">
            <h2 class="h6">Tren harian: pendapatan dan laba bersih</h2>
            <canvas id="chTren" height="150"></canvas>
        </div>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6"><div class="ad-card p-3"><h2 class="h6">Pesanan per jam</h2><canvas id="chJam" height="130"></canvas></div></div>
    <div class="col-md-6"><div class="ad-card p-3"><h2 class="h6">Pesanan per hari dalam seminggu</h2><canvas id="chHari" height="130"></canvas></div></div>
</div>

<div class="row g-3 mb-3">
    <div class="col-lg-4">
        <div class="ad-card p-3 h-100">
            <h2 class="h6">Indikator lain</h2>
            <ul class="list-unstyled mb-0 small">
                <li class="d-flex justify-content-between py-1 border-bottom"><span>Jumlah pesanan selesai</span><strong>{{ $f['sales']['order_count'] }}</strong></li>
                <li class="d-flex justify-content-between py-1 border-bottom"><span>Rata-rata nilai per pesanan</span><strong>{{ rupiah($f['sales']['avg_order']) }}</strong></li>
                <li class="d-flex justify-content-between py-1 border-bottom"><span>Porsi terjual</span><strong>{{ $f['sales']['items_sold'] }}</strong></li>
                <li class="d-flex justify-content-between py-1 border-bottom"><span>Tingkat pembatalan</span><strong>{{ $f['cancel_rate'] }}%</strong></li>
                @foreach($f['sales']['by_channel'] as $c)
                    <li class="d-flex justify-content-between py-1 border-bottom"><span>{{ $c['label'] }}</span><strong>{{ rupiah($c['revenue']) }}</strong></li>
                @endforeach
            </ul>
        </div>
    </div>
    <div class="col-lg-8">
        <div class="ad-card p-3 h-100">
            <h2 class="h6">Kontribusi laba per menu</h2>
            <div class="table-responsive" style="max-height: 340px; overflow:auto">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Menu</th><th class="text-end">Porsi</th><th class="text-end">Pendapatan</th><th class="text-end">Laba</th><th class="text-end">Margin</th></tr></thead>
                    <tbody>
                    @forelse($f['menu_profit'] as $m)
                        <tr><td>{{ $m['name'] }}</td><td class="text-end">{{ $m['qty'] }}</td><td class="text-end">{{ rupiah($m['revenue']) }}</td><td class="text-end">{{ rupiah($m['profit']) }}</td><td class="text-end">{{ $m['margin'] }}%</td></tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted">Belum ada data.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    (function () {
        var rp = function (v) { return 'Rp ' + Number(v).toLocaleString('id-ID'); };
        var daily = @json(collect($f['daily'])->map(fn ($d) => ['t' => $d['date']->format('d/m'), 'rev' => $d['revenue'], 'net' => $d['net']])->values());
        new Chart(document.getElementById('chTren'), {
            type: 'line',
            data: { labels: daily.map(function (d) { return d.t; }), datasets: [
                { label: 'Pendapatan', data: daily.map(function (d) { return d.rev; }), borderColor: '#a3173f', backgroundColor: 'rgba(163,23,63,.1)', tension: .25, fill: true },
                { label: 'Laba bersih', data: daily.map(function (d) { return d.net; }), borderColor: '#198754', tension: .25 }
            ]},
            options: { plugins: { tooltip: { callbacks: { label: function (c) { return c.dataset.label + ': ' + rp(c.parsed.y); } } } }, scales: { y: { ticks: { callback: rp } } } }
        });
        var jam = @json(array_values($f['by_hour']));
        new Chart(document.getElementById('chJam'), {
            type: 'bar',
            data: { labels: jam.map(function (_, i) { return String(i).padStart(2, '0'); }), datasets: [{ label: 'Pesanan', data: jam, backgroundColor: '#f2a73b' }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
        var hari = @json(array_values($f['by_weekday']));
        new Chart(document.getElementById('chHari'), {
            type: 'bar',
            data: { labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'], datasets: [{ label: 'Pesanan', data: hari, backgroundColor: '#a3173f' }] },
            options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });
    })();
</script>
@endpush
