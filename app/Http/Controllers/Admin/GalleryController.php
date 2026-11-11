<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GalleryPhoto;
use App\Support\ImageUploader;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        return view('admin.gallery', [
            'photos' => GalleryPhoto::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'caption' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        GalleryPhoto::create([
            'image' => ImageUploader::store($request->file('photo'), 'gallery'),
            'caption' => $data['caption'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        return back()->with('success', 'Foto ditambahkan ke galeri.');
    }

    public function update(Request $request, GalleryPhoto $photo)
    {
        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:120'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $photo->update(['caption' => $data['caption'] ?? null, 'sort_order' => $data['sort_order'] ?? 0]);

        return back()->with('success', 'Informasi foto diperbarui.');
    }

    public function destroy(GalleryPhoto $photo)
    {
        ImageUploader::delete($photo->image);
        $photo->delete();

        return back()->with('success', 'Foto dihapus.');
    }
}
