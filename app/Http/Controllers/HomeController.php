<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\GalleryPhoto;
use App\Models\Product;
use App\Models\Promo;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function home()
    {
        $featured = Product::active()->with('category')->where('is_featured', true)->orderBy('name')->limit(6)->get();
        if ($featured->isEmpty()) {
            $featured = Product::active()->with('category')->orderBy('id')->limit(6)->get();
        }

        return view('public.home', [
            'featured' => $featured,
            'promos' => Promo::running()->orderBy('ends_on')->limit(3)->get(),
            'photos' => GalleryPhoto::orderBy('sort_order')->orderBy('id')->limit(6)->get(),
        ]);
    }

    public function menu(Request $request)
    {
        $categories = Category::orderBy('sort_order')->orderBy('name')->get();
        $slug = $request->query('kategori');
        $aktif = $categories->firstWhere('slug', $slug);

        $products = Product::active()->with('category')
            ->when($aktif, fn ($q) => $q->where('category_id', $aktif->id))
            ->orderBy('category_id')->orderBy('name')
            ->get();

        return view('public.menu', compact('categories', 'products', 'aktif'));
    }

    public function promo()
    {
        return view('public.promo', [
            'promos' => Promo::running()->orderBy('ends_on')->get(),
        ]);
    }

    public function gallery()
    {
        return view('public.gallery', [
            'photos' => GalleryPhoto::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function contact()
    {
        return view('public.contact');
    }

    public function sitemap()
    {
        $halaman = [
            route('home'), route('menu'), route('promo'), route('gallery'), route('contact'),
        ];

        return response()->view('public.sitemap', ['halaman' => $halaman])
            ->header('Content-Type', 'application/xml');
    }
}
