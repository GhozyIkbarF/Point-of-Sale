<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        $cashiers = User::where('role', 'cashier')->get();
        $products = Product::all();

        if ($cashiers->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Generate sales untuk beberapa hari
        $dates = [
            now()->subDays(2),
            now()->subDay(),
            now(),
        ];

        $customers = [
            'John Doe', 'Jane Smith', 'Bob Johnson', 'Alice Brown', 'Charlie Wilson',
            'Diana Davis', 'Eva Martinez', 'Frank Anderson', 'Grace Lee', 'Henry Taylor'
        ];

        $invoiceCounter = 1;

        foreach ($dates as $date) {
            $transactionsPerDay = rand(8, 15);
            
            for ($i = 0; $i < $transactionsPerDay; $i++) {
                $cashier = $cashiers->random();
                $customer = $customers[array_rand($customers)];
                
                // Random jam transaksi (8 AM - 9 PM)
                $randomHour = rand(8, 21);
                $randomMinute = rand(0, 59);
                $transactionTime = $date->copy()->setTime($randomHour, $randomMinute);
                
                // Generate invoice number
                $invoiceNumber = 'INV-' . $transactionTime->format('ymd') . '-' . str_pad($invoiceCounter++, 3, '0', STR_PAD_LEFT);
                
                // Random 1 hingga jumlah produk yang tersedia (max 4)
                $maxItems = min(4, $products->count());
                $itemCount = rand(1, $maxItems);
                $selectedProducts = $products->random($itemCount);
                
                $totalPrice = 0;
                $saleItems = [];
                
                // Calculate total price dan prepare items
                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 3);
                    $subtotal = $product->price * $qty;
                    $totalPrice += $subtotal;
                    
                    $saleItems[] = [
                        'product' => $product,
                        'qty' => $qty,
                        'price_at_time' => $product->price,
                    ];
                }
                
                // Random pembayaran (exact, atau lebih)
                $paidAmount = rand(0, 1) ? $totalPrice : $totalPrice + rand(5000, 50000);
                $change = $paidAmount - $totalPrice;
                
                // Create sale
                $sale = Sale::create([
                    'invoice_number' => $invoiceNumber,
                    'customer_name' => $customer,
                    'cashier_id' => $cashier->id,
                    'total_price' => $totalPrice,
                    'paid_amount' => $paidAmount,
                    'change' => $change,
                    'created_at' => $transactionTime,
                    'updated_at' => $transactionTime,
                ]);
                
                // Create sale details
                foreach ($saleItems as $item) {
                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product']->id,
                        'qty' => $item['qty'],
                        'price_at_time' => $item['price_at_time'],
                        'created_at' => $transactionTime,
                        'updated_at' => $transactionTime,
                    ]);
                    
                    // Update stock
                    $item['product']->decrement('stock', $item['qty']);
                }
            }
        }
        
        // Tambahan sales hari ini untuk testing real-time
        $this->generateTodaySales($cashiers, $products, $customers, $invoiceCounter);
    }
    
    private function generateTodaySales($cashiers, $products, $customers, &$invoiceCounter)
    {
        $today = now();
        $hoursToGenerate = [9, 10, 11, 13, 14, 15, 16, 17, 18, 19, 20];
        
        foreach ($hoursToGenerate as $hour) {
            // 60% chance untuk generate sale di jam ini
            if (rand(1, 100) <= 60) {
                $cashier = $cashiers->random();
                $customer = $customers[array_rand($customers)];
                
                $transactionTime = $today->copy()->setTime($hour, rand(0, 59));
                $invoiceNumber = 'INV-' . $transactionTime->format('ymd') . '-' . str_pad($invoiceCounter++, 3, '0', STR_PAD_LEFT);
                
                // Variasi item (1 hingga jumlah produk yang tersedia, max 3)
                $maxItems = min(3, $products->count());
                $itemCount = rand(1, $maxItems);
                $selectedProducts = $products->random($itemCount);
                
                $totalPrice = 0;
                $saleItems = [];
                
                foreach ($selectedProducts as $product) {
                    $qty = rand(1, 2);
                    $subtotal = $product->price * $qty;
                    $totalPrice += $subtotal;
                    
                    $saleItems[] = [
                        'product' => $product,
                        'qty' => $qty,
                        'price_at_time' => $product->price,
                    ];
                }
                
                $paidAmount = $totalPrice + rand(0, 20000);
                $change = $paidAmount - $totalPrice;
                
                $sale = Sale::create([
                    'invoice_number' => $invoiceNumber,
                    'customer_name' => $customer,
                    'cashier_id' => $cashier->id,
                    'total_price' => $totalPrice,
                    'paid_amount' => $paidAmount,
                    'change' => $change,
                    'created_at' => $transactionTime,
                    'updated_at' => $transactionTime,
                ]);
                
                foreach ($saleItems as $item) {
                    SaleDetail::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product']->id,
                        'qty' => $item['qty'],
                        'price_at_time' => $item['price_at_time'],
                        'created_at' => $transactionTime,
                        'updated_at' => $transactionTime,
                    ]);
                    
                    $item['product']->decrement('stock', $item['qty']);
                }
            }
        }
    }
}
