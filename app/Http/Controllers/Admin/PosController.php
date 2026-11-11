<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Services\OrderService;
use DomainException;
use Illuminate\Http\Request;

class PosController extends Controller
{
    /** Input pesanan di warung dengan tombol besar (FR-16). */
    public function create()
    {
        $categories = Category::with(['products' => fn ($q) => $q->orderable()->orderBy('name')])
            ->orderBy('sort_order')->get()
            ->filter(fn ($c) => $c->products->isNotEmpty());

        return view('admin.pos', compact('categories'));
    }

    public function store(Request $request, OrderService $service)
    {
        $data = $request->validate([
            'items' => ['required', 'array'],
            'items.*' => ['nullable', 'integer', 'min:0', 'max:50'],
            'payment_method' => ['required', 'in:tunai,transfer,qris'],
            'fulfillment' => ['required', 'in:makan_ditempat,ambil'],
            'customer_name' => ['nullable', 'string', 'max:80'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        $qty = [];
        foreach ($data['items'] as $productId => $jumlah) {
            if ((int) $jumlah > 0) {
                $qty[(int) $productId] = (int) $jumlah;
            }
        }

        if (! $qty) {
            return back()->withInput()->with('error', 'Pilih minimal satu menu.');
        }

        try {
            $order = $service->createOffline(
                $qty, $request->user(), $data['payment_method'],
                $data['customer_name'] ?? null, $data['notes'] ?? null, $data['fulfillment']
            );
        } catch (DomainException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        return redirect()->route('admin.orders.show', $order)
            ->with('success', 'Pesanan '.$order->code.' tersimpan sebagai Selesai dan lunas ('.rupiah($order->total).').');
    }
}
