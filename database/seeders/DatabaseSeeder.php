<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'role' => 'donatur',
            ]
        );

        $this->call([
            DemoDataSeeder::class,
            // KarhutlaFundraisingSeeder sengaja tidak dipanggil: data demo
            // karhutla sudah dihapus agar spesies hanya Mahoni, Kaliandra,
            // dan Sengon. File seeder tetap disimpan bila ingin dipakai lagi.
        ]);
    }
}
