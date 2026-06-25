<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaultCategories = [
            ['name' => 'أولاد', 'type' => 'expense', 'icon' => 'child_care', 'user_id' => null],
            ['name' => 'مواصلات', 'type' => 'expense', 'icon' => 'directions_bus', 'user_id' => null],
            ['name' => 'تسوق', 'type' => 'expense', 'icon' => 'shopping_cart', 'user_id' => null],
            ['name' => 'راتب', 'type' => 'income', 'icon' => 'payments', 'user_id' => null],
            ['name' => 'طعام', 'type' => 'expense', 'icon' => 'fastfood', 'user_id' => null],
        ];

        foreach ($defaultCategories as $category) {
            \App\Models\Category::updateOrCreate(
                ['name' => $category['name'], 'user_id' => null], // شرط البحث
                $category // البيانات المراد إدخالها
            );
        }
    }
}
