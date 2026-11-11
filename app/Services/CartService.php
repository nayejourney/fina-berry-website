<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

/** Keranjang berbasis sesi. Hanya menyimpan id menu dan jumlah; harga selalu dibaca ulang dari database. */
class CartService
{
    private const KEY = 'cart.items';
    private const MAX_PER_ITEM = 50;

    /** @return array<int,int> [product_id => qty] */
    public function items(): array
    {
        return array_filter((array) session(self::KEY, []), fn ($qty) => (int) $qty > 0);
    }

    public function add(int $productId, int $qty = 1): void
    {
        $items = $this->items();
        $items[$productId] = min(self::MAX_PER_ITEM, ($items[$productId] ?? 0) + max(1, $qty));
        session([self::KEY => $items]);
    }

    public function set(int $productId, int $qty): void
    {
        if ($qty <= 0) {
            $this->remove($productId);

            return;
        }

        $items = $this->items();
        $items[$productId] = min(self::MAX_PER_ITEM, $qty);
        session([self::KEY => $items]);
    }

    public function remove(int $productId): void
    {
        $items = $this->items();
        unset($items[$productId]);
        session([self::KEY => $items]);
    }

    public function clear(): void
    {
        session()->forget(self::KEY);
    }

    public function count(): int
    {
        return (int) array_sum($this->items());
    }

    /** @return Collection<int,array{product:Product,qty:int,line_total:int,orderable:bool}> */
    public function lines(): Collection
    {
        $items = $this->items();
        if (! $items) {
            return collect();
        }

        $products = Product::with('category')->whereIn('id', array_keys($items))->get()->keyBy('id');
        $lines = collect();

        foreach ($items as $id => $qty) {
            $product = $products->get($id);
            if (! $product) {
                $this->remove((int) $id);
                continue;
            }

            $lines->push([
                'product' => $product,
                'qty' => (int) $qty,
                'line_total' => $product->price * (int) $qty,
                'orderable' => $product->is_orderable,
            ]);
        }

        return $lines;
    }

    public function subtotal(): int
    {
        return (int) $this->lines()->where('orderable', true)->sum('line_total');
    }

    /** Hapus item yang habis atau nonaktif dari keranjang, kembalikan nama-namanya (E-03). */
    public function removeUnavailable(): array
    {
        $names = [];
        foreach ($this->lines()->where('orderable', false) as $line) {
            $names[] = $line['product']->name;
            $this->remove($line['product']->id);
        }

        return $names;
    }
}
