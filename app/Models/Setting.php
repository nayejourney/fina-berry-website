<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    private static ?array $cache = null;

    /** Nilai bawaan; bisa diubah Pemilik lewat halaman Pengaturan Toko (FR-20). */
    public const DEFAULTS = [
        'store_name' => 'Warung Makan Fina Berry',
        'tagline' => 'Rasa rumahan yang tak terlupakan, sejak 2012',
        'about' => 'Warung Fina Berry berdiri sejak 2012 dan hadir dengan berbagai pilihan menu yang lezat, berkualitas, dan terjangkau. Setiap hidangan dimasak dengan penuh dedikasi, supaya setiap suapan membawa kehangatan masakan rumah.',
        'wa_number' => '6285647731631',
        'address' => "Kawasan D'LAS (Desa Wisata Lembah Asri), dekat pintu keluar, Desa Serang, Kecamatan Karangreja, Kabupaten Purbalingga, Jawa Tengah.",
        'open_hours' => '08.00 - 17.00 WIB',
        'maps_query' => "Warung Makan Fina Berry D'LAS Desa Serang Karangreja Purbalingga",
        'instagram' => 'https://www.instagram.com/fina.berry',
        'facebook' => '',
        'tiktok' => '',
        'halal_cert_number' => '',
        'wa_greeting' => 'Halo Warung Makan Fina Berry, saya ingin memesan:',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        if (static::$cache === null) {
            try {
                static::$cache = static::query()->pluck('value', 'key')->all();
            } catch (\Throwable) {
                static::$cache = [];
            }
        }

        // Kolom yang sengaja dikosongkan Pemilik tetap kosong (tidak kembali ke nilai bawaan).
        if (array_key_exists($key, static::$cache)) {
            return (string) static::$cache[$key];
        }

        return $default ?? (self::DEFAULTS[$key] ?? null);
    }

    public static function put(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        static::$cache = null;
    }

    public static function flush(): void
    {
        static::$cache = null;
    }
}
