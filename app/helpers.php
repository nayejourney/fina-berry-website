<?php

use App\Models\Setting;

if (! function_exists('rupiah')) {
    /** Format angka ke Rupiah, contoh: Rp 15.000 */
    function rupiah(int|float|string|null $nilai): string
    {
        return 'Rp '.number_format((float) $nilai, 0, ',', '.');
    }
}

if (! function_exists('setting')) {
    /** Ambil nilai pengaturan toko. */
    function setting(string $key, ?string $default = null): ?string
    {
        return Setting::get($key, $default);
    }
}

if (! function_exists('wa_number')) {
    /** Normalisasi nomor WhatsApp ke format internasional tanpa tanda plus (62xxxxxxxx). */
    function wa_number(?string $nomor): string
    {
        $digit = preg_replace('/\D+/', '', (string) $nomor);
        if ($digit === '') {
            return '';
        }
        if (str_starts_with($digit, '0')) {
            return '62'.substr($digit, 1);
        }
        if (str_starts_with($digit, '8')) {
            return '62'.$digit;
        }

        return $digit;
    }
}
