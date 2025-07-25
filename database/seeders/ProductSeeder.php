<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['sku' => 'SKU001', 'name' => 'Kopi Hitam', 'price' => 10000, 'stock' => 100],
            ['sku' => 'SKU002', 'name' => 'Teh Botol', 'price' => 8000, 'stock' => 180],
            ['sku' => 'SKU003', 'name' => 'Air Mineral', 'price' => 5000, 'stock' => 150],
            ['sku' => 'SKU004', 'name' => 'Kopi Susu', 'price' => 12000, 'stock' => 175],
            ['sku' => 'SKU005', 'name' => 'Teh Tarik', 'price' => 9000, 'stock' => 160],
            ['sku' => 'SKU006', 'name' => 'Jus Jeruk', 'price' => 15000, 'stock' => 140],
            ['sku' => 'SKU007', 'name' => 'Cappuccino', 'price' => 18000, 'stock' => 150],
            ['sku' => 'SKU008', 'name' => 'Latte', 'price' => 20000, 'stock' => 145],
            ['sku' => 'SKU009', 'name' => 'Espresso', 'price' => 16000, 'stock' => 155],
            ['sku' => 'SKU010', 'name' => 'Americano', 'price' => 14000, 'stock' => 165],
            ['sku' => 'SKU011', 'name' => 'Mocha', 'price' => 22000, 'stock' => 135],
            ['sku' => 'SKU012', 'name' => 'Macchiato', 'price' => 24000, 'stock' => 130],
            ['sku' => 'SKU013', 'name' => 'Frappuccino', 'price' => 26000, 'stock' => 125],
            ['sku' => 'SKU014', 'name' => 'Green Tea', 'price' => 11000, 'stock' => 170],
            ['sku' => 'SKU015', 'name' => 'Chocolate', 'price' => 17000, 'stock' => 140],
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
