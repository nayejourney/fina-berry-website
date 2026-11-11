<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $judul }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container text-center py-5">
    <div class="display-1 fw-bold" style="color:#a3173f">{{ $kode }}</div>
    <h1 class="h3 mb-2">{{ $judul }}</h1>
    <p class="text-muted">{{ $pesan }}</p>
    <a href="{{ url('/') }}" class="btn btn-dark">Kembali ke beranda</a>
</div>
</body>
</html>
