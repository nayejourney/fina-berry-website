<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function show(CartService $cart)
    {
        $lines = $cart->lines();
        if ($lines->isEmpty()) {
            return redirect()->route('menu')->with('error', 'Keranjang masih kosong. Pilih menu dulu, ya.');
        }

        if (! session()->has('checkout.key')) {
            session(['checkout.key' => (string) Str::uuid()]);
        }

        return view('public.checkout', [
            'lines' => $lines,
            'subtotal' => $cart->subtotal(),
            'key' => session('checkout.key'),
        ]);
    }

    public function store(Request $request, CartService $cart, OrderService $orders)
    {
        // Rapikan nomor WhatsApp sebelum divalidasi (spasi, tanda hubung, titik dibuang).
        $request->merge([
            'customer_phone' => preg_replace('/[\s\-\.]/', '', (string) $request->input('customer_phone')),
        ]);

        $data = $request->validate([
            'customer_name' => ['required', 'string', 'max:80'],
            'customer_phone' => ['required', 'regex:/^(\+?62|0)8[0-9]{7,12}$/'],
            'fulfillment' => ['required', 'in:ambil,antar'],
            'address' => ['required_if:fulfillment,antar', 'nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:tunai,transfer,qris'],
            'consent' => ['accepted'],
            'idempotency_key' => ['required', 'string', 'max:64'],
        ], [
            'customer_phone.regex' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890.',
            'consent.accepted' => 'Anda perlu menyetujui pemberitahuan privasi untuk melanjutkan.',
            'address.required_if' => 'Alamat pengantaran wajib diisi jika memilih diantar.',
        ]);

        // Menu yang habis atau dinonaktifkan dikeluarkan dari keranjang dan pembeli diberi tahu (E-03).
        $dihapus = $cart->removeUnavailable();
        if ($dihapus) {
            return redirect()->route('cart')->with('error', 'Menu berikut baru saja habis dan dikeluarkan dari keranjang: '.implode(', ', $dihapus).'.');
        }

        try {
            $order = $orders->createOnline($data, $cart->items(), $data['idempotency_key']);
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        $cart->clear();
        session()->forget('checkout.key');

        return redirect()->route('order.confirmation', $order->public_token)->with('auto_wa', true);
    }

    public function confirmation(string $token, OrderService $orders)
    {
        $order = Order::where('public_token', $token)->with('items')->firstOrFail();

        return view('public.confirmation', [
            'order' => $order,
            'waUrl' => $orders->waUrl($order),
            'waMessage' => $orders->waMessage($order),
        ]);
    }
}
