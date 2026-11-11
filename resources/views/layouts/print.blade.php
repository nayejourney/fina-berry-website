<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'Cetak')</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="{{ asset('css/print.css') }}" rel="stylesheet">
    @stack('head')
</head>
<body>
<div class="print-wrap">
    <div class="print-actions no-print">
        <button onclick="window.print()" style="padding:8px 14px;font-weight:600;cursor:pointer">Cetak / simpan sebagai PDF</button>
        <button onclick="window.close()" style="padding:8px 14px;cursor:pointer">Tutup</button>
        <span style="color:#666;font-size:12px;margin-left:8px">Di jendela cetak, pilih "Simpan sebagai PDF" untuk menyimpan berkas PDF.</span>
    </div>
    @yield('content')
</div>
@stack('scripts')
</body>
</html>
