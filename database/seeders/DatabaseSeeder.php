<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Category::factory(1000)->create();
        // Transaction::factory(1000)->create();
        User::updateOrCreate(
            ['email' => 'admin@expensra.com'], // لو موجود ميكررهوش، لو مش موجود يكريته
            [
                'first_name' => 'Mohamed',
                'last_name' => 'Nageh',
                'email' => 'admin@expensra.com',
                'password' => Hash::make('password123'), // اختار باسورد قوية
                'system_role' => 'admin',
                'email_verified_at' => now(),
            ]
        );
        
    }
}
