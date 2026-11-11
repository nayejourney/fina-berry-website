<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Simpan foto unggahan: diperkecil dan dikonversi ke WebP (jika ekstensi GD tersedia),
 * dengan nama acak. Metadata EXIF ikut terbuang karena gambar dienkode ulang (NFR-02, A08).
 */
class ImageUploader
{
    public static function store(UploadedFile $file, string $dir, int $maxSisi = 1200): string
    {
        $dir = trim($dir, '/');
        $nama = Str::random(32);

        if (function_exists('imagewebp') && function_exists('imagecreatefromstring')) {
            $biner = @file_get_contents($file->getRealPath());
            $gambar = $biner ? @imagecreatefromstring($biner) : false;

            if ($gambar !== false) {
                $lebar = imagesx($gambar);
                $tinggi = imagesy($gambar);

                if (max($lebar, $tinggi) > $maxSisi) {
                    $skala = $maxSisi / max($lebar, $tinggi);
                    $baru = imagescale($gambar, (int) round($lebar * $skala), (int) round($tinggi * $skala));
                    if ($baru !== false) {
                        $gambar = $baru;
                    }
                }

                imagepalettetotruecolor($gambar);
                imagealphablending($gambar, true);
                imagesavealpha($gambar, true);

                ob_start();
                imagewebp($gambar, null, 80);
                $webp = ob_get_clean();

                if ($webp) {
                    $path = "uploads/{$dir}/{$nama}.webp";
                    Storage::disk('public')->put($path, $webp);

                    return $path;
                }
            }
        }

        // Cadangan: simpan berkas asli (sudah divalidasi sebagai gambar) dengan nama acak.
        $ekstensi = in_array(strtolower($file->extension()), ['jpg', 'jpeg', 'png', 'webp'], true)
            ? strtolower($file->extension()) : 'jpg';

        return $file->storeAs("uploads/{$dir}", "{$nama}.{$ekstensi}", 'public');
    }

    /** Hapus berkas unggahan lama. Berkas bawaan seeder (images/...) tidak disentuh. */
    public static function delete(?string $path): void
    {
        if ($path && str_starts_with($path, 'uploads/')) {
            Storage::disk('public')->delete($path);
        }
    }
}
