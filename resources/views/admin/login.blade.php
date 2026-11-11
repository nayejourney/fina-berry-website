<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Masuk Admin | {{ setting('store_name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('css/admin.css') }}" rel="stylesheet">
</head>
<body>
<div class="container py-5" style="max-width: 430px;">
    <div class="text-center mb-4">
        <img src="{{ asset('images/logo.webp') }}" alt="" width="72" height="72" class="rounded-circle mb-2">
        <h1 class="h4 mb-0">{{ setting('store_name') }}</h1>
        <div class="text-muted">Masuk ke dashboard admin</div>
    </div>
    <div class="ad-card p-4">
        @if($errors->any())
            <div class="alert alert-danger py-2">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login.attempt') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" required autofocus autocomplete="username">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Kata sandi</label>
                <input type="password" id="password" name="password" class="form-control" required autocomplete="current-password">
            </div>
            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1">
                <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
            </div>
            <button class="btn btn-primary w-100">Masuk</button>
        </form>
    </div>
    <p class="text-center mt-3"><a href="{{ route('home') }}" class="small">&larr; Kembali ke website</a></p>
</div>
</body>
</html>
