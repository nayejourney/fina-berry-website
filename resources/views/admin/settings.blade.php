@extends('layouts.admin')

@section('title', 'Pengaturan toko')

@section('content')
<form method="POST" action="{{ route('admin.settings.update') }}" class="ad-card p-3 p-md-4" style="max-width: 820px;">
    @csrf @method('PUT')
    <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="store_name">Nama usaha</label>
            <input type="text" id="store_name" name="store_name" value="{{ old('store_name', $s['store_name']) }}" class="form-control" maxlength="80" required></div>
        <div class="col-md-6"><label class="form-label" for="wa_number">Nomor WhatsApp pemesanan</label>
            <input type="text" id="wa_number" name="wa_number" value="{{ old('wa_number', $s['wa_number']) }}" class="form-control" placeholder="081234567890" required>
            <div class="form-text">Tujuan tombol WhatsApp dan pesan pesanan. Format 08xx atau 62xx.</div></div>
        <div class="col-12"><label class="form-label" for="tagline">Tagline</label>
            <input type="text" id="tagline" name="tagline" value="{{ old('tagline', $s['tagline']) }}" class="form-control" maxlength="160"></div>
        <div class="col-12"><label class="form-label" for="about">Tentang usaha</label>
            <textarea id="about" name="about" rows="3" class="form-control" maxlength="800">{{ old('about', $s['about']) }}</textarea></div>
        <div class="col-12"><label class="form-label" for="address">Alamat</label>
            <textarea id="address" name="address" rows="2" class="form-control" maxlength="300">{{ old('address', $s['address']) }}</textarea></div>
        <div class="col-md-6"><label class="form-label" for="open_hours">Jam buka</label>
            <input type="text" id="open_hours" name="open_hours" value="{{ old('open_hours', $s['open_hours']) }}" class="form-control" maxlength="120"></div>
        <div class="col-md-6"><label class="form-label" for="maps_query">Kata kunci peta (Google Maps)</label>
            <input type="text" id="maps_query" name="maps_query" value="{{ old('maps_query', $s['maps_query']) }}" class="form-control" maxlength="200"></div>
        <div class="col-md-4"><label class="form-label" for="instagram">Instagram (URL)</label>
            <input type="url" id="instagram" name="instagram" value="{{ old('instagram', $s['instagram']) }}" class="form-control" maxlength="200"></div>
        <div class="col-md-4"><label class="form-label" for="facebook">Facebook (URL)</label>
            <input type="url" id="facebook" name="facebook" value="{{ old('facebook', $s['facebook']) }}" class="form-control" maxlength="200"></div>
        <div class="col-md-4"><label class="form-label" for="tiktok">TikTok (URL)</label>
            <input type="url" id="tiktok" name="tiktok" value="{{ old('tiktok', $s['tiktok']) }}" class="form-control" maxlength="200"></div>
        <div class="col-md-6"><label class="form-label" for="halal_cert_number">Nomor sertifikat halal</label>
            <input type="text" id="halal_cert_number" name="halal_cert_number" value="{{ old('halal_cert_number', $s['halal_cert_number']) }}" class="form-control" maxlength="60">
            <div class="form-text">Kosongkan jika belum ada. Label halal hanya tampil jika kolom ini diisi.</div></div>
        <div class="col-12"><label class="form-label" for="wa_greeting">Kalimat pembuka pesan WhatsApp</label>
            <input type="text" id="wa_greeting" name="wa_greeting" value="{{ old('wa_greeting', $s['wa_greeting']) }}" class="form-control" maxlength="200" required></div>
    </div>
    <button class="btn btn-primary mt-4">Simpan pengaturan</button>
</form>
@endsection
