<?php

namespace App\Support;

use Symfony\Component\HttpFoundation\StreamedResponse;

class Csv
{
    /** Unduh CSV (UTF-8 dengan BOM agar terbuka rapi di Excel). */
    public static function download(string $filename, array $rows): StreamedResponse
    {
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            foreach ($rows as $row) {
                // Cegah CSV injection: sel teks yang diawali = + - @ diberi tanda kutip.
                $row = array_map(function ($v) {
                    if (is_string($v) && $v !== '' && preg_match('/^[=+\-@\t\r]/', $v)) {
                        return "'".$v;
                    }

                    return $v;
                }, $row);
                fputcsv($out, $row, ';');
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
