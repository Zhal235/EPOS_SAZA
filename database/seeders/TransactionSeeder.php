<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Product;
use App\Models\User;

class TransactionSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();
        $products = Product::take(5)->get();

        if (!$user || $products->isEmpty()) {
            $this->command->warn('No users or products found. Please run users and products seeders first.');
            return;
        }

        // Create 10 sample transactions
        for ($i = 1; $i <= 10; $i++) {
            $subtotal = 0;
            $transactionDate = now()->subDays(rand(0, 7))->subHours(rand(0, 23));
            
            $transaction = Transaction::create([
                'transaction_number' => '', // Let it auto-generate
                'user_id' => $user->id,
                'customer_name' => $i <= 3 ? 'Walk-in Customer' : 'Customer ' . $i,
                'customer_phone' => $i > 3 ? '081234567' . str_pad($i, 3, '0', STR_PAD_LEFT) : null,
                'subtotal' => 0, // Will be updated after items
                'tax_amount' => 0, // Will be calculated
                'discount_amount' => $i % 3 == 0 ? 5000 : 0,
                'total_amount' => 0, // Will be calculated
                'paid_amount' => 0, // Will be calculated
                'change_amount' => 0,
                'payment_method' => ['cash', 'qris', 'card', 'rfid'][array_rand(['cash', 'qris', 'card', 'rfid'])],
                'status' => 'completed',
                'created_at' => $transactionDate,
                'updated_at' => $transactionDate,
            ]);

            // Add 1-4 random items to each transaction
            $itemCount = rand(1, 4);
            for ($j = 0; $j < $itemCount; $j++) {
                $product = $products->random();
                $quantity = rand(1, 3);
                $unitPrice = $product->selling_price;
                $totalPrice = $quantity * $unitPrice;

                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'total_price' => $totalPrice,
                ]);

                $subtotal += $totalPrice;
            }

            // Calculate final amounts
            $totalAmount = $subtotal - $transaction->discount_amount; // No tax

            $transaction->update([
                'subtotal' => $subtotal,
                'tax_amount' => 0, // No tax
                'total_amount' => $totalAmount,
                'paid_amount' => $totalAmount, // Exact payment
            ]);
        }

        $this->command->info('Created 10 sample transactions with items.');
    }
}
