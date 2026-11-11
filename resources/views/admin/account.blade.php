@extends('layouts.admin')

@section('title', 'Akun saya')

@section('content')
<div class="ad-card p-3 p-md-4 mb-3" style="max-width: 520px;">
    <div class="small text-muted">Masuk sebagai</div>
    <div class="fw-bold">{{ auth()->user()->name }}</div>
    <div>{{ auth()->user()->email }} &middot; <span class="badge text-bg-secondary">{{ auth()->user()->role_label }}</span></div>
</div>
<form method="POST" action="{{ route('admin.account.update') }}" class="ad-card p-3 p-md-4" style="max-width: 520px;">
    @csrf @method('PUT')
    <h2 class="h6">Ganti kata sandi</h2>
    <div class="mb-3"><label class="form-label" for="current_password">Kata sandi saat ini</label>
        <input type="password" id="current_password" name="current_password" class="form-control" required autocomplete="current-password"></div>
    <div class="mb-3"><label class="form-label" for="password">Kata sandi baru</label>
        <input type="password" id="password" name="password" class="form-control" required autocomplete="new-password">
        <div class="form-text">Minimal 8 karakter, mengandung huruf dan angka.</div></div>
    <div class="mb-3"><label class="form-label" for="password_confirmation">Ulangi kata sandi baru</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required autocomplete="new-password"></div>
    <button class="btn btn-primary">Simpan</button>
</form>
@endsection
