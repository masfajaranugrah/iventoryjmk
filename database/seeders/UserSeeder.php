<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@inventori.test'],
            [
                'name' => 'Admin Inventori',
                'password' => 'password',
                'role' => 'admin',
            ]
        );

        User::updateOrCreate(
            ['email' => 'petugas@inventori.test'],
            [
                'name' => 'Petugas Gudang',
                'password' => 'password',
                'role' => 'petugas',
            ]
        );
    }
}
