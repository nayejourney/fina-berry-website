<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class QrController extends Controller
{
    /** QR Code ke katalog digital, siap cetak (FR-19). */
    public function show()
    {
        return view('admin.qr', ['url' => route('menu')]);
    }
}
