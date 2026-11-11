<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $values = [];
        foreach (array_keys(Setting::DEFAULTS) as $key) {
            $values[$key] = Setting::get($key);
        }

        return view('admin.settings', ['s' => $values]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'store_name' => ['required', 'string', 'max:80'],
            'tagline' => ['nullable', 'string', 'max:160'],
            'about' => ['nullable', 'string', 'max:800'],
            'wa_number' => ['required', 'regex:/^[0-9+\s\-]{9,20}$/'],
            'address' => ['nullable', 'string', 'max:300'],
            'open_hours' => ['nullable', 'string', 'max:120'],
            'maps_query' => ['nullable', 'string', 'max:200'],
            'instagram' => ['nullable', 'url', 'max:200'],
            'facebook' => ['nullable', 'url', 'max:200'],
            'tiktok' => ['nullable', 'url', 'max:200'],
            'halal_cert_number' => ['nullable', 'string', 'max:60'],
            'wa_greeting' => ['required', 'string', 'max:200'],
        ], [
            'wa_number.regex' => 'Nomor WhatsApp tidak valid. Contoh: 081234567890.',
        ]);

        $data['wa_number'] = wa_number($data['wa_number']);

        $changed = [];
        foreach ($data as $key => $value) {
            if ((string) Setting::get($key) !== (string) $value) {
                $changed[] = $key;
            }
            Setting::put($key, $value);
        }

        if ($changed) {
            AuditLog::record('settings.updated', null, ['keys' => $changed]);
        }

        return back()->with('success', 'Pengaturan toko disimpan.');
    }
}
