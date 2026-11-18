<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Chicken Adobo Rice', 'price' => '89.00', 'stock_quantity' => 20],
            ['name' => 'Pork Sisig Rice', 'price' => '105.00', 'stock_quantity' => 15],
            ['name' => 'Cheeseburger', 'price' => '75.00', 'stock_quantity' => 18],
            ['name' => 'Pancit Canton', 'price' => '65.00', 'stock_quantity' => 12],
            ['name' => 'Banana Cue', 'price' => '25.00', 'stock_quantity' => 25],
            ['name' => 'Iced Calamansi', 'price' => '35.00', 'stock_quantity' => 30],
        ];

        foreach ($products as $product) {
            Product::firstOrCreate(
                ['name' => $product['name']],
                $product
            );
        }

        Customer::firstOrCreate(
            ['email' => 'sample.customer@example.com'],
            [
                'full_name' => 'Sample Customer',
                'phone' => null,
            ]
        );
    }
}