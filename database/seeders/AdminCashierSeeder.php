<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminCashierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::create([
            'name' => 'Admin',
            'email' => 'admin@minipos.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create kasir user
        User::create([
            'name' => 'Kasir 1',
            'email' => 'kasir1@minipos.com', 
            'password' => Hash::make('password'),
            'role' => 'cashier',
        ]);

        User::create([
            'name' => 'Kasir 2',
            'email' => 'kasir2@minipos.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
        ]);
    }
}
