<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Support\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->query('kategori'), fn ($q, $v) => $q->where('category_id', $v))
            ->when($request->query('q'), fn ($q, $v) => $q->where('name', 'like', '%'.$v.'%'))
            ->when($request->query('status') === 'nonaktif', fn ($q) => $q->where('is_active', false))
            ->orderBy('category_id')->orderBy('name')
            ->get();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    /** Staf dan Pemilik: tandai menu habis atau tersedia dalam satu langkah (E-09). */
    public function toggle(Product $product)
    {
        $product->update(['is_available' => ! $product->is_available]);

        return back()->with('success', $product->name.' ditandai '.($product->is_available ? 'tersedia' : 'habis').'.');
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new Product(['is_available' => true, 'is_active' => true]),
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);

        if ($request->hasFile('photo')) {
            $data['image'] = ImageUploader::store($request->file('photo'), 'products');
        }

        $product = Product::create($data);
        AuditLog::record('product.created', $product, ['price' => $product->price]);

        return redirect()->route('admin.products.index')->with('success', 'Menu '.$product->name.' ditambahkan.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => Category::orderBy('sort_order')->get(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request);
        $oldPrice = $product->price;
        $oldCost = $product->cost_price;

        if ($data['name'] !== $product->name) {
            $data['slug'] = $this->uniqueSlug($data['name'], $product->id);
        }

        if ($request->hasFile('photo')) {
            ImageUploader::delete($product->image);
            $data['image'] = ImageUploader::store($request->file('photo'), 'products');
        }

        $product->update($data);

        if ($oldPrice !== $product->price || $oldCost !== $product->cost_price) {
            AuditLog::record('product.price_changed', $product, [
                'price' => [$oldPrice, $product->price],
                'cost_price' => [$oldCost, $product->cost_price],
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', 'Menu '.$product->name.' diperbarui.');
    }

    /** Menu yang pernah dipesan dinonaktifkan, bukan dihapus permanen (FR-12). */
    public function destroy(Product $product)
    {
        if ($product->orderItems()->exists()) {
            $product->update(['is_active' => false]);
            AuditLog::record('product.deactivated', $product);

            return back()->with('success', $product->name.' pernah dipesan, jadi dinonaktifkan (riwayat pesanan tetap utuh).');
        }

        ImageUploader::delete($product->image);
        AuditLog::record('product.deleted', $product, ['name' => $product->name]);
        $product->delete();

        return back()->with('success', 'Menu dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'price' => ['required', 'integer', 'min:0', 'max:10000000'],
            'cost_price' => ['required', 'integer', 'min:0', 'max:10000000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        unset($data['photo']);
        $data['is_available'] = $request->boolean('is_available');
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');

        return $data;
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'menu';
        $slug = $base;
        $i = 2;
        while (Product::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
