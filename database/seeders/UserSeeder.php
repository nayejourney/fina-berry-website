<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /** Akun awal untuk tiap peran. Password dari SEED_DEFAULT_PASSWORD di .env, WAJIB diganti setelah login pertama. */
    public function run(): void
    {
        $password = env('SEED_DEFAULT_PASSWORD', 'GantiSaya#2026');

        $akun = [
            ['Bos Fina Berry', 'bos@finaberry.com', User::ROLE_SUPER_ADMIN],
            ['Pemilik Fina Berry', 'pemilik@finaberry.test', User::ROLE_OWNER],
            ['Staf Warung', 'staf@finaberry.test', User::ROLE_STAFF],
        ];

        foreach ($akun as [$nama, $email, $role]) {
            User::firstOrCreate(
                ['email' => $email],
                ['name' => $nama, 'password' => $password, 'role' => $role, 'is_active' => true]
            );
        }
    }
}
