<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'name' => 'Administrator Toko',
                'username' => 'admin',
                'email' => 'admin@tokoretail.com',
                'password' => Hash::make('password123'),
                'role' => 'admin',
                'is_active' => true,
            ],
            [
                'name' => 'Petugas Gudang',
                'username' => 'gudang',
                'email' => 'gudang@tokoretail.com',
                'password' => Hash::make('password123'),
                'role' => 'gudang',
                'is_active' => true,
            ],
            [
                'name' => 'Kasir Utama',
                'username' => 'kasir',
                'email' => 'kasir@tokoretail.com',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'is_active' => true,
            ],
            [
                'name' => 'Kasir Nonaktif (Demo Test)',
                'username' => 'kasir_nonaktif',
                'email' => 'kasir_nonaktif@tokoretail.com',
                'password' => Hash::make('password123'),
                'role' => 'kasir',
                'is_active' => false,
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['username' => $userData['username']],
                $userData
            );
        }
    }
}
