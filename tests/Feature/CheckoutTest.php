<?php

namespace Tests\Feature;

use App\Models\Order;

class CheckoutTest extends FinaBerryTestCase
{
    public function test_checkout_menyimpan_pesanan_menunggu_dengan_total_dari_server(): void
    {
        $p = $this->product('Bakso', 15000);

        $this->post('/keranjang', ['product_id' => $p->id, 'qty' => 2]);
        $res = $this->post('/checkout', $this->checkoutPayload());

        $order = Order::first();
        $this->assertNotNull($order);
        $res->assertRedirect(route('order.confirmation', $order->public_token));
        $this->assertSame('menunggu', $order->status);
        $this->assertSame('belum_bayar', $order->payment_status);
        $this->assertSame('online', $order->channel);
        $this->assertSame(30000, $order->total);
        $this->assertStringStartsWith('FB-'.now()->format('Ymd').'-', $order->code);
    }

    public function test_harga_pesanan_lama_tidak_berubah_saat_harga_menu_diubah(): void
    {
        $p = $this->product('Bakso', 15000);
        $this->post('/keranjang', ['product_id' => $p->id, 'qty' => 1]);
        $this->post('/checkout', $this->checkoutPayload());

        $p->update(['price' => 99000]);

        $item = Order::first()->items->first();
        $this->assertSame(15000, $item->unit_price);
        $this->assertSame(15000, Order::first()->total);
    }

    public function test_klik_ganda_dengan_kunci_sama_hanya_membuat_satu_pesanan(): void
    {
        $p = $this->product();
        $this->post('/keranjang', ['product_id' => $p->id]);
        $this->post('/checkout', $this->checkoutPayload());
        $this->post('/keranjang', ['product_id' => $p->id]);
        $this->post('/checkout', $this->checkoutPayload());

        $this->assertSame(1, Order::count());
    }

    public function test_menu_habis_ditolak_dan_dikeluarkan_dari_keranjang(): void
    {
        $p = $this->product();
        $this->post('/keranjang', ['product_id' => $p->id]);
        $p->update(['is_available' => false]);

        $res = $this->post('/checkout', $this->checkoutPayload());

        $res->assertRedirect(route('cart'));
        $res->assertSessionHas('error');
        $this->assertSame(0, Order::count());
    }

    public function test_input_berbahaya_disimpan_sebagai_teks_biasa(): void
    {
        $p = $this->product();
        $this->post('/keranjang', ['product_id' => $p->id]);
        $this->post('/checkout', $this->checkoutPayload(['customer_name' => "' OR 1=1 --", 'notes' => '<script>alert(1)</script>']));

        $order = Order::first();
        $this->assertSame("' OR 1=1 --", $order->customer_name);

        $this->get(route('order.confirmation', $order->public_token))
            ->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_checkout_wajib_persetujuan_privasi_dan_nomor_valid(): void
    {
        $p = $this->product();
        $this->post('/keranjang', ['product_id' => $p->id]);

        $res = $this->post('/checkout', $this->checkoutPayload(['consent' => null, 'customer_phone' => '123']));

        $res->assertSessionHasErrors(['consent', 'customer_phone']);
        $this->assertSame(0, Order::count());
    }
}
