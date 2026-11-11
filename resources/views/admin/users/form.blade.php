@extends('layouts.admin')

@section('title', $user->exists ? 'Ubah akun' : 'Tambah akun')

@section('content')
<form method="POST" class="ad-card p-3 p-md-4" style="max-width: 620px;"
      action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}">
    @csrf
    @if($user->exists) @method('PUT') @endif
    <div class="mb-3"><label class="form-label" for="name">Nama</label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="form-control" maxlength="80" required></div>
    <div class="mb-3"><label class="form-label" for="email">Email (untuk login)</label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="form-control" maxlength="120" required autocomplete="off"></div>
    <div class="mb-3"><label class="form-label" for="role">Peran</label>
        <select id="role" name="role" class="form-select">
            @foreach($roles as $k => $v)<option value="{{ $k }}" @selected(old('role', $user->role) === $k)>{{ $v }}</option>@endforeach
        </select>
        <div class="form-text">Staf: pesanan dan kasir. Pemilik: + konten, laporan penjualan, biaya. Super Admin: + laba rugi dan analitik.</div></div>
    <div class="row g-3 mb-3">
        <div class="col-md-6"><label class="form-label" for="password">{{ $user->exists ? 'Kata sandi baru (opsional)' : 'Kata sandi' }}</label>
            <input type="password" id="password" name="password" class="form-control" autocomplete="new-password" {{ $user->exists ? '' : 'required' }}></div>
        <div class="col-md-6"><label class="form-label" for="password_confirmation">Ulangi kata sandi</label>
            <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password"></div>
        <div class="form-text">Minimal 8 karakter, mengandung huruf dan angka.</div>
    </div>
    @if($user->exists)
        <div class="form-check mb-3"><input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" @checked(old('is_active', $user->is_active))><label class="form-check-label" for="is_active">Akun aktif</label></div>
    @endif
    <button class="btn btn-primary">Simpan</button> <a href="{{ route('admin.users.index') }}" class="btn btn-light">Batal</a>
</form>
@endsection
