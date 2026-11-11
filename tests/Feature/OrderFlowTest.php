<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Promo;
use App\Services\OrderService;
use App\Services\ReportService;

class OrderFlowTest extends FinaBerryTestCase
{
    private function onlineOrder(int $price = 20000, int $cost = 10000): Order
    {
        $p = $this->product('Soto', $price, $cost);

        return app(OrderService::class)->createOnline(
            ['customer_name' => 'Ani', 'customer_phone' => '081200000001', 'fulfillment' => 'ambil', 'payment_method' => 'tunai'],
            [$p->id => 2],
            'kunci-'.uniqid()
        );
    }

    public function test_pesanan_tidak_bisa_selesai_sebelum_pembayaran_divalidasi(): void
    {
        $staf = $this->user('staff');
        $order = $this->onlineOrder();
        $svc = app(OrderService::class);

        $svc->changeStatus($order, 'diproses', $staf);
        $this->expectException(\DomainException::class);
        $svc->changeStatus($order->fresh(), 'selesai', $staf);
    }

    public function test_pembatalan_wajib_beralasan_dan_tidak_masuk_pendapatan(): void
    {
        $staf = $this->user('staff');
        $order = $this->onlineOrder();
        $svc = app(OrderService::class);

        try {
            $svc->changeStatus($order, 'dibatalkan', $staf, '');
            $this->fail('Seharusnya ditolak tanpa alasan.');
        } catch (\DomainException) {
            $this->assertSame('menunggu', $order->fresh()->status);
        }

        $svc->changeStatus($order, 'dibatalkan', $staf, 'Pelanggan tidak merespons');
        $this->assertSame(0, Order::counted()->count());
    }

    public function test_alur_lengkap_masuk_laporan_dan_laba_rugi(): void
    {
        $staf = $this->user('staff');
        $order = $this->onlineOrder(20000, 10000); // 2 porsi: pendapatan 40.000, HPP 20.000
        $svc = app(OrderService::class);

        $svc->changeStatus($order, 'diproses', $staf);
        $svc->validatePayment($order->fresh(), 'qris', null, $staf);
        $svc->changeStatus($order->fresh(), 'selesai', $staf);

        $p = $this->product('Es Teh', 5000, 1000);
        $svc->createOffline([$p->id => 3], $staf, 'tunai', null, null, 'makan_ditempat'); // 15.000, HPP 3.000

        [$from, $to] = [now()->startOfDay(), now()->endOfDay()];
        $f = app(ReportService::class)->financial($from, $to);

        $this->assertSame(55000, $f['revenue']);
        $this->assertSame(23000, $f['cogs']);
        $this->assertSame(32000, $f['gross_profit']);
        $this->assertSame(32000, $f['net_profit']); // belum ada biaya operasional
        $this->assertSame(2, $f['sales']['order_count']);
        $this->assertSame(1, $f['sales']['by_channel']['offline']['orders']);
        $this->assertSame(1, $f['sales']['by_channel']['online']['orders']);
    }

    public function test_hanya_pemilik_yang_boleh_mengoreksi_pesanan_selesai(): void
    {
        $staf = $this->user('staff');
        $owner = $this->user('owner');
        $p = $this->product();
        $order = app(OrderService::class)->createOffline([$p->id => 1], $staf, 'tunai', null, null, 'ambil');
        $svc = app(OrderService::class);

        try {
            $svc->changeStatus($order, 'dibatalkan', $staf, 'salah input pesanan');
            $this->fail('Staf tidak boleh mengoreksi.');
        } catch (\DomainException) {
            $this->assertSame('selesai', $order->fresh()->status);
        }

        $svc->changeStatus($order, 'dibatalkan', $owner, 'salah input pesanan');
        $this->assertSame('dibatalkan', $order->fresh()->status);
        $this->assertDatabaseHas('audit_logs', ['action' => 'order.correction']);
    }

    public function test_promo_kedaluwarsa_tidak_tampil_di_halaman_publik(): void
    {
        Promo::create(['title' => 'Promo Lama', 'starts_on' => today()->subDays(10), 'ends_on' => today()->subDay(), 'is_active' => true]);
        Promo::create(['title' => 'Promo Berjalan', 'starts_on' => today()->subDay(), 'ends_on' => today()->addDays(3), 'is_active' => true]);

        $this->get('/promo')->assertOk()->assertSee('Promo Berjalan')->assertDontSee('Promo Lama');
    }

    public function test_menu_dengan_riwayat_pesanan_dinonaktifkan_bukan_dihapus(): void
    {
        $owner = $this->user('owner');
        $order = $this->onlineOrder();
        $product = $order->items->first()->product;

        $this->actingAs($owner)->delete('/admin/menu/'.$product->id)->assertRedirect();

        $this->assertDatabaseHas('products', ['id' => $product->id, 'is_active' => false]);
    }
}
