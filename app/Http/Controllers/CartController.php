<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(CartService $cart)
    {
        return view('public.cart', [
            'lines' => $cart->lines(),
            'subtotal' => $cart->subtotal(),
        ]);
    }

    public function add(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        if (! $product->is_orderable) {
            return back()->with('error', $product->name.' sedang tidak tersedia.');
        }

        $cart->add($product->id, (int) ($data['qty'] ?? 1));

        return back()->with('success', $product->name.' ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartService $cart)
    {
        $data = $request->validate([
            'qty' => ['required', 'array'],
            'qty.*' => ['integer', 'min:0', 'max:50'],
        ]);

        foreach ($data['qty'] as $productId => $qty) {
            $cart->set((int) $productId, (int) $qty);
        }

        return redirect()->route('cart')->with('success', 'Keranjang diperbarui.');
    }

    public function remove(Product $product, CartService $cart)
    {
        $cart->remove($product->id);

        return redirect()->route('cart')->with('success', 'Item dihapus dari keranjang.');
    }
}
