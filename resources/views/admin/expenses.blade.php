@extends('layouts.admin')

@section('title', 'Biaya operasional')

@section('content')
<div class="row g-3">
    <div class="col-lg-4">
        <form method="POST" action="{{ route('admin.expenses.store') }}" class="ad-card p-3">@csrf
            <h2 class="h6">Catat biaya</h2>
            <label class="form-label small mb-1">Tanggal</label>
            <input type="date" name="spent_on" value="{{ old('spent_on', today()->toDateString()) }}" max="{{ today()->toDateString() }}" class="form-control mb-2" required>
            <label class="form-label small mb-1">Kategori</label>
            <select name="category" class="form-select mb-2">@foreach(\App\Models\Expense::CATEGORIES as $k => $v)<option value="{{ $k }}" @selected(old('category') === $k)>{{ $v }}</option>@endforeach</select>
            <label class="form-label small mb-1">Keterangan</label>
            <input type="text" name="description" value="{{ old('description') }}" class="form-control mb-2" maxlength="160" required placeholder="Contoh: gas elpiji 3 kg">
            <label class="form-label small mb-1">Jumlah (Rp)</label>
            <input type="number" name="amount" value="{{ old('amount') }}" class="form-control mb-3" min="1" step="1" required>
            <button class="btn btn-primary w-100">Simpan</button>
            <div class="form-text mt-2">Biaya di luar HPP menu (gaji, gas, listrik, sewa, dan lain-lain) dipakai untuk menghitung laba bersih.</div>
        </form>
    </div>
    <div class="col-lg-8">
        <form method="GET" class="ad-card p-3 mb-3 d-flex flex-wrap gap-2 align-items-end">
            <div><label class="form-label small mb-1">Dari</label><input type="date" name="dari" value="{{ $from->toDateString() }}" class="form-control form-control-sm"></div>
            <div><label class="form-label small mb-1">Sampai</label><input type="date" name="sampai" value="{{ $to->toDateString() }}" class="form-control form-control-sm"></div>
            <button class="btn btn-sm btn-primary">Tampilkan</button>
            <div class="ms-auto text-end"><div class="small text-muted">Total periode</div><div class="fs-5 fw-bold">{{ rupiah($total) }}</div></div>
        </form>
        <div class="ad-card p-3">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead><tr><th>Tanggal</th><th>Kategori</th><th>Keterangan</th><th class="text-end">Jumlah</th><th></th></tr></thead>
                    <tbody>
                    @forelse($expenses as $e)
                        <tr>
                            <td class="text-nowrap">{{ $e->spent_on->format('d/m/Y') }}</td>
                            <td>{{ $e->category_label }}</td>
                            <td>{{ $e->description }}<div class="small text-muted">{{ $e->user?->name }}</div></td>
                            <td class="text-end">{{ rupiah($e->amount) }}</td>
                            <td class="text-end"><form method="POST" action="{{ route('admin.expenses.destroy', $e) }}" onsubmit="return confirm('Hapus catatan biaya ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">Belum ada biaya pada periode ini.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
