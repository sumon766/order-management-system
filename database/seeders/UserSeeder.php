<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'System Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Product Vendor',
            'email' => 'vendor@example.com',
            'password' => Hash::make('password'),
            'role' => 'vendor',
        ]);

        User::create([
            'name' => 'Test Customer',
            'email' => 'customer@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
        ]);

        User::factory(5)->create(['role' => 'vendor']);
        User::factory(10)->create(['role' => 'customer']);
    }
}
