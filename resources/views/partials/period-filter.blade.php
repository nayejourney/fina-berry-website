{{-- Variabel: $route (nama rute), $from, $to --}}
<form method="GET" action="{{ route($route) }}" class="ad-card p-3 mb-3">
    <div class="d-flex flex-wrap gap-2 align-items-end">
        <div><label class="form-label small mb-1">Dari</label><input type="date" name="dari" value="{{ $from->toDateString() }}" class="form-control form-control-sm"></div>
        <div><label class="form-label small mb-1">Sampai</label><input type="date" name="sampai" value="{{ $to->toDateString() }}" class="form-control form-control-sm"></div>
        <button class="btn btn-sm btn-primary">Tampilkan</button>
        <div class="d-flex flex-wrap gap-1 ms-md-3">
            <a class="btn btn-sm btn-outline-secondary" href="{{ route($route, ['dari' => today()->toDateString(), 'sampai' => today()->toDateString()]) }}">Hari ini</a>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route($route, ['dari' => today()->subDays(6)->toDateString(), 'sampai' => today()->toDateString()]) }}">7 hari</a>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route($route, ['dari' => today()->startOfMonth()->toDateString(), 'sampai' => today()->toDateString()]) }}">Bulan ini</a>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route($route, ['dari' => today()->subMonthNoOverflow()->startOfMonth()->toDateString(), 'sampai' => today()->subMonthNoOverflow()->endOfMonth()->toDateString()]) }}">Bulan lalu</a>
        </div>
    </div>
</form>
