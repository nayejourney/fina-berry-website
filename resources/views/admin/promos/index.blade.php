@extends('layouts.admin')

@section('title', 'Promo')

@section('content')
<div class="mb-3"><a href="{{ route('admin.promos.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah promo</a></div>
<div class="ad-card p-3">
    <table class="table align-middle mb-0">
        <thead><tr><th>Promo</th><th>Periode</th><th>Status</th><th></th></tr></thead>
        <tbody>
        @forelse($promos as $p)
            <tr>
                <td class="fw-semibold">{{ $p->title }}<div class="small text-muted">{{ \Illuminate\Support\Str::limit($p->description, 80) }}</div></td>
                <td class="text-nowrap">{{ $p->starts_on->format('d/m/Y') }} - {{ $p->ends_on->format('d/m/Y') }}</td>
                <td>
                    @if($p->is_running)<span class="badge text-bg-success">Tampil di website</span>
                    @elseif(! $p->is_active)<span class="badge text-bg-secondary">Nonaktif</span>
                    @elseif($p->ends_on->lt(today()))<span class="badge text-bg-secondary">Sudah berakhir</span>
                    @else<span class="badge text-bg-info">Belum mulai</span>@endif
                </td>
                <td class="text-end text-nowrap">
                    <a href="{{ route('admin.promos.edit', $p) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                    <form method="POST" action="{{ route('admin.promos.destroy', $p) }}" class="d-inline" onsubmit="return confirm('Hapus promo ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button></form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada promo.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
