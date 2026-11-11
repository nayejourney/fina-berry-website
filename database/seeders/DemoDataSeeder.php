<?php

namespace Database\Seeders;

use App\Models\Expense;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Seeder;

/**
 * DATA DEMO (acak) untuk memperlihatkan laporan dan analitik. Jangan dijalankan di produksi.
 * Jalankan: php artisan db:seed --class=DemoDataSeeder
 */
class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $staf = User::where('role', User::ROLE_STAFF)->first() ?? User::first();
        $produk = Product::orderable()->get();
        $service = app(OrderService::class);
        mt_srand(2026);

        for ($hari = 30; $hari >= 0; $hari--) {
            $jumlah = mt_rand(6, 16);
            for ($i = 0; $i < $jumlah; $i++) {
                $qty = [];
                foreach ($produk->random(mt_rand(1, 3)) as $p) {
                    $qty[$p->id] = mt_rand(1, 3);
                }

                $metode = ['tunai', 'tunai', 'tunai', 'qris', 'transfer'][mt_rand(0, 4)];
                $order = $service->createOffline($qty, $staf, $metode, null, null, 'makan_ditempat');

                $waktu = now()->subDays($hari)->setTime(mt_rand(8, 16), mt_rand(0, 59));
                $online = mt_rand(1, 100) <= 30;
                $order->forceFill([
                    'code' => 'FB-'.$waktu->format('Ymd').'-D'.str_pad((string) $order->id, 4, '0', STR_PAD_LEFT),
                    'created_at' => $waktu,
                    'updated_at' => $waktu,
                    'paid_at' => $waktu,
                    'completed_at' => $waktu,
                    'channel' => $online ? Order::CHANNEL_ONLINE : Order::CHANNEL_OFFLINE,
                    'customer_name' => $online ? 'Pelanggan Demo' : null,
                    'customer_phone' => $online ? '081200000000' : null,
                    'fulfillment' => $online ? 'ambil' : 'makan_ditempat',
                ])->save();
            }

            // Sesekali ada pesanan batal.
            if (mt_rand(1, 100) <= 25) {
                $p = $produk->random();
                $o = $service->createOffline([$p->id => 1], $staf, 'tunai', null, null, 'ambil');
                $o->forceFill(['code' => 'FB-'.now()->subDays($hari)->format('Ymd').'-D'.str_pad((string) $o->id, 4, '0', STR_PAD_LEFT),
                    'status' => Order::STATUS_CANCELLED, 'payment_status' => Order::PAYMENT_UNPAID,
                    'cancel_reason' => 'Pelanggan batal (data demo)', 'completed_at' => null,
                    'created_at' => now()->subDays($hari)->setTime(10, 0)])->save();
            }
        }

        // Biaya operasional bulanan dan harian (contoh).
        for ($bulan = 0; $bulan <= 1; $bulan++) {
            $tgl = now()->subMonths($bulan)->startOfMonth()->addDays(1);
            if ($tgl->isFuture()) {
                continue;
            }
            Expense::create(['spent_on' => $tgl, 'category' => 'gaji', 'description' => 'Gaji karyawan (demo)', 'amount' => 1800000]);
            Expense::create(['spent_on' => $tgl, 'category' => 'sewa', 'description' => 'Sewa tempat (demo)', 'amount' => 600000]);
        }
        for ($hari = 28; $hari >= 0; $hari -= 7) {
            Expense::create(['spent_on' => now()->subDays($hari), 'category' => 'operasional', 'description' => 'Gas dan listrik (demo)', 'amount' => 150000]);
            Expense::create(['spent_on' => now()->subDays($hari), 'category' => 'bahan_baku', 'description' => 'Belanja bumbu dan sayur (demo)', 'amount' => 250000]);
        }
    }
}
