@extends('layouts.admin')

@section('title', 'Log aktivitas')

@section('content')
<div class="ad-card p-3">
    <div class="table-responsive">
        <table class="table table-sm align-middle table-nowrap mb-0">
            <thead><tr><th>Waktu</th><th>Pengguna</th><th>Aksi</th><th>Objek</th><th>Detail</th></tr></thead>
            <tbody>
            @forelse($logs as $l)
                <tr>
                    <td>{{ $l->created_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $l->user?->name ?? 'Sistem' }}</td>
                    <td><code>{{ $l->action }}</code></td>
                    <td>{{ $l->entity_type }}{{ $l->entity_id ? ' #'.$l->entity_id : '' }}</td>
                    <td class="small text-muted text-wrap" style="max-width:420px">{{ $l->details ? json_encode($l->details, JSON_UNESCAPED_UNICODE) : '' }}</td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted py-4">Belum ada aktivitas tercatat.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $logs->links() }}</div>
</div>
@endsection
