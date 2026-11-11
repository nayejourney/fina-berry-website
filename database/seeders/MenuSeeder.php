<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Data menu dan harga diambil dari proyek lama (SRS). Kolom HPP (cost_price) di bawah adalah
 * PERKIRAAN SEMENTARA (sekitar 55% dari harga jual) hanya agar laporan laba/rugi bisa didemokan.
 * Pemilik wajib mengisi HPP asli lewat halaman Menu sebelum angka laba dipakai.
 */
class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = ['Makanan' => 1, 'Minuman' => 2, 'Cemilan' => 3];
        $cat = [];
        foreach ($kategori as $nama => $urut) {
            $cat[$nama] = Category::firstOrCreate(['slug' => Str::slug($nama)], ['name' => $nama, 'sort_order' => $urut]);
        }

        $paket = 'Termasuk nasi, lauk, dan lalapan sambal.';
        $menu = [
            // [kategori, nama, harga, deskripsi, file foto, unggulan]
            ['Makanan', 'Bakso Daging Sapi', 15000, 'Bakso sapi kenyal dengan kuah kaldu gurih, mie, dan topping lengkap.', 'bakso-daging-sapi', true],
            ['Makanan', 'Bakso Krewedan', 22000, null, 'bakso-krewedan', false],
            ['Makanan', 'Bakso Keju', 17000, null, 'bakso-keju', false],
            ['Makanan', 'Soto Ayam', 10000, null, 'soto-ayam', true],
            ['Makanan', 'Soto Ayam Komplit', 20000, null, 'soto-ayam-komplit', true],
            ['Makanan', 'Soto Daging Sapi', 23000, null, 'soto-daging-sapi', false],
            ['Makanan', 'Soto Babat', 20000, null, 'soto-babat', false],
            ['Makanan', 'Nasi Goreng', 15000, null, 'nasi-goreng', false],
            ['Makanan', 'Sop Daging Sapi + Nasi', 25000, null, null, false],
            ['Makanan', 'Ca Kangkung', 8000, null, 'ca-kangkung', false],
            ['Makanan', 'Paket Ayam Goreng', 15000, $paket, 'paket-ayam-goreng', false],
            ['Makanan', 'Paket Ayam Bakar', 15000, $paket, 'paket-ayam-bakar', false],
            ['Makanan', 'Paket Ayam Goreng Penyet', 16000, $paket, 'paket-ayam-penyet', false],
            ['Makanan', 'Paket Ayam Bakar Penyet', 16000, $paket, 'paket-ayam-penyet', false],
            ['Makanan', 'Paket Ayam Goreng Kampung', 25000, $paket, 'paket-ayam-kampung', true],
            ['Makanan', 'Paket Ayam Bakar Kampung', 27000, $paket, 'paket-ayam-bakar-kampung', true],
            ['Makanan', 'Paket Ayam Goreng Kampung Penyet', 27000, $paket, 'paket-ayam-kampung-penyet', false],
            ['Makanan', 'Paket Ayam Bakar Kampung Penyet', 27000, $paket, 'paket-ayam-bakar-kampung-penyet', false],
            ['Makanan', 'Paket Ikan Mujair Goreng', 20000, $paket, 'paket-ikan-goreng', false],
            ['Makanan', 'Paket Ikan Mujair Bakar', 20000, $paket, 'paket-ikan-bakar', false],

            ['Minuman', 'Teh Tawar', 2000, null, 'teh-tawar', false],
            ['Minuman', 'Es Teh Tawar', 3000, null, 'es-teh-tawar', false],
            ['Minuman', 'Teh Manis Anget', 3000, null, 'teh-manis-anget', false],
            ['Minuman', 'Es Teh Manis', 4000, null, 'es-teh-manis', false],
            ['Minuman', 'Lemon Tea Anget', 9000, null, 'lemon-tea-anget', false],
            ['Minuman', 'Es Lemon Tea', 10000, null, 'es-lemon-tea', false],
            ['Minuman', 'Jeruk Anget', 4000, null, 'jeruk-anget', false],
            ['Minuman', 'Es Jeruk', 5000, null, 'es-jeruk', false],
            ['Minuman', 'Aneka Kopi', 3000, null, 'aneka-kopi', false],
            ['Minuman', 'Kopi Cappucino', 5000, null, 'kopi-cappucino', false],
            ['Minuman', 'Es Kopi Cappucino', 5000, null, 'es-kopi-cappucino', false],
            ['Minuman', 'Jahe Susu', 3000, null, 'jahe-susu', false],
            ['Minuman', 'Soda Susu', 10000, null, 'soda-susu', false],
            ['Minuman', 'Wedang Jahe', 7000, null, 'wedang-jahe', false],
            ['Minuman', 'Jus Strawberry', 12000, null, 'jus-strawberry', false],
            ['Minuman', 'Jus Melon', 10000, null, null, false],
            ['Minuman', 'Jus Jambu', 10000, null, 'jus-jambu', false],
            ['Minuman', 'Jus Mangga', 12000, null, null, false],
            ['Minuman', 'Jus Jeruk', 10000, null, null, false],
            ['Minuman', 'Jus Buah Naga', 10000, null, 'jus-buah-naga', false],
            ['Minuman', 'Jus Alpukat', 12000, null, null, false],

            ['Cemilan', 'Mendoan', 10000, '1 porsi.', 'mendoan', true],
            ['Cemilan', 'Tahu Brontak', 10000, '1 porsi.', 'tahu-brontak', false],
            ['Cemilan', 'Pisang Goreng', 10000, '1 porsi.', 'pisang-goreng', false],
        ];

        foreach ($menu as [$kat, $nama, $harga, $desc, $foto, $unggulan]) {
            Product::firstOrCreate(
                ['slug' => Str::slug($nama)],
                [
                    'category_id' => $cat[$kat]->id,
                    'name' => $nama,
                    'description' => $desc,
                    'price' => $harga,
                    'cost_price' => (int) (round($harga * 0.55 / 500) * 500), // PERKIRAAN, ganti dengan HPP asli
                    'image' => $foto ? "images/menu/{$foto}.webp" : null,
                    'is_available' => true,
                    'is_active' => true,
                    'is_featured' => $unggulan,
                ]
            );
        }
    }
}
