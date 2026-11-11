<?php

namespace Database\Seeders;

use App\Models\GalleryPhoto;
use Illuminate\Database\Seeder;

class GallerySeeder extends Seeder
{
    public function run(): void
    {
        $foto = [
            ['images/warung.webp', 'Suasana Warung Fina Berry', 1],
            ['images/galeri-bakso.webp', 'Bakso daging sapi', 2],
            ['images/galeri-soto.webp', 'Soto ayam komplit', 3],
            ['images/galeri-ayam.webp', 'Ayam bakar kampung', 4],
            ['images/galeri-mendoan.webp', 'Mendoan hangat', 5],
            ['images/about.webp', 'Warung Fina Berry', 6],
        ];

        foreach ($foto as [$path, $caption, $urut]) {
            GalleryPhoto::firstOrCreate(['image' => $path], ['caption' => $caption, 'sort_order' => $urut]);
        }
    }
}
