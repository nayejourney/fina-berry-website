<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

abstract class FinaBerryTestCase extends TestCase
{
    use RefreshDatabase;

    protected function user(string $role): User
    {
        return User::create([
            'name' => ucfirst($role), 'email' => $role.'@test.local', 'password' => 'Rahasia123', 'role' => $role, 'is_active' => true,
        ]);
    }

    protected function product(string $name = 'Bakso', int $price = 15000, int $cost = 8000, array $extra = []): Product
    {
        $cat = Category::firstOrCreate(['slug' => 'makanan'], ['name' => 'Makanan', 'sort_order' => 1]);

        return Product::create($extra + [
            'category_id' => $cat->id, 'name' => $name, 'slug' => str($name)->slug().'-'.uniqid(),
            'price' => $price, 'cost_price' => $cost, 'is_available' => true, 'is_active' => true,
        ]);
    }

    protected function checkoutPayload(array $override = []): array
    {
        return $override + [
            'customer_name' => 'Budi', 'customer_phone' => '081234567890', 'fulfillment' => 'ambil',
            'payment_method' => 'tunai', 'consent' => '1', 'idempotency_key' => 'kunci-uji-0001',
        ];
    }
}
