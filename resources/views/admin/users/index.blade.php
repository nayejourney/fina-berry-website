@extends('layouts.admin')

@section('title', 'Akun pengguna')

@section('content')
<div class="mb-3"><a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm"><i class="bi bi-plus-lg"></i> Tambah akun</a></div>
<div class="ad-card p-3">
    <div class="table-responsive">
        <table class="table align-middle table-nowrap mb-0">
            <thead><tr><th>Nama</th><th>Email</th><th>Peran</th><th>Status</th><th>Login terakhir</th><th></th></tr></thead>
            <tbody>
            @foreach($users as $usr)
                <tr>
                    <td class="fw-semibold">{{ $usr->name }} @if($usr->id === $actor->id)<span class="badge text-bg-light border">Anda</span>@endif</td>
                    <td>{{ $usr->email }}</td>
                    <td><span class="badge text-bg-secondary">{{ $usr->role_label }}</span></td>
                    <td>{!! $usr->is_active ? '<span class="badge text-bg-success">Aktif</span>' : '<span class="badge text-bg-danger">Nonaktif</span>' !!}</td>
                    <td>{{ $usr->last_login_at ? $usr->last_login_at->format('d/m/Y H:i') : '-' }}</td>
                    <td class="text-end">
                        @if($actor->isSuperAdmin() || $usr->role === 'staff')
                            <a href="{{ route('admin.users.edit', $usr) }}" class="btn btn-sm btn-outline-primary">Ubah</a>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @unless($actor->isSuperAdmin())<p class="small text-muted mt-2 mb-0">Pemilik hanya dapat mengelola akun Staf. Akun Pemilik dan Super Admin dikelola Super Admin.</p>@endunless
</div>
@endsection
