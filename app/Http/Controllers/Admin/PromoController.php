<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Promo;
use App\Support\ImageUploader;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        return view('admin.promos.index', ['promos' => Promo::orderByDesc('ends_on')->get()]);
    }

    public function create()
    {
        return view('admin.promos.form', ['promo' => new Promo([
            'is_active' => true, 'starts_on' => today(), 'ends_on' => today()->addDays(7),
        ])]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            $data['image'] = ImageUploader::store($request->file('photo'), 'promos');
        }

        Promo::create($data);

        return redirect()->route('admin.promos.index')->with('success', 'Promo ditambahkan.');
    }

    public function edit(Promo $promo)
    {
        return view('admin.promos.form', compact('promo'));
    }

    public function update(Request $request, Promo $promo)
    {
        $data = $this->validated($request);

        if ($request->hasFile('photo')) {
            ImageUploader::delete($promo->image);
            $data['image'] = ImageUploader::store($request->file('photo'), 'promos');
        }

        $promo->update($data);

        return redirect()->route('admin.promos.index')->with('success', 'Promo diperbarui.');
    }

    public function destroy(Promo $promo)
    {
        ImageUploader::delete($promo->image);
        $promo->delete();

        return back()->with('success', 'Promo dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:600'],
            'starts_on' => ['required', 'date'],
            'ends_on' => ['required', 'date', 'after_or_equal:starts_on'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        unset($data['photo']);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
