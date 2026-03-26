<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds performance indexes to improve query speed
     * especially for dashboard statistics and POS operations.
     */
    public function up(): void
    {
        // Transaction indexes for faster queries
        Schema::table('transactions', function (Blueprint $table) {
            // For date-based filtering (used in dashboard & reports)
            if (!$this->indexExists('transactions', 'transactions_created_at_index')) {
                $table->index('created_at');
            }
            
            // For status-based queries with date (dashboard, statistics)
            if (!$this->indexExists('transactions', 'transactions_status_created_at_index')) {
                $table->index(['status', 'created_at']);
            }
            
            // For payment method statistics
            if (!$this->indexExists('transactions', 'transactions_payment_method_status_index')) {
                $table->index(['payment_method', 'status']);
            }
            
            // For cashier performance queries
            if (!$this->indexExists('transactions', 'transactions_user_id_index')) {
                $table->index('user_id');
            }
        });

        // Transaction items indexes
        Schema::table('transaction_items', function (Blueprint $table) {
            // For date-based reporting
            if (!$this->indexExists('transaction_items', 'transaction_items_created_at_index')) {
                $table->index('created_at');
            }
            
            // For product sales report
            if (!$this->indexExists('transaction_items', 'transaction_items_product_id_created_at_index')) {
                $table->index(['product_id', 'created_at']);
            }
        });

        // Products indexes for faster POS loading
        Schema::table('products', function (Blueprint $table) {
            // For outlet mode filtering (store/foodcourt)
            if (!$this->indexExists('products', 'products_outlet_type_is_active_index')) {
                $table->index(['outlet_type', 'is_active']);
            }
            
            // For stock-aware product loading
            if (!$this->indexExists('products', 'products_outlet_type_is_active_stock_quantity_index')) {
                $table->index(['outlet_type', 'is_active', 'stock_quantity']);
            }
            
            // For product search by name
            if (!$this->indexExists('products', 'products_name_index')) {
                $table->index('name');
            }
        });

        // Categories indexes
        Schema::table('categories', function (Blueprint $table) {
            // For sidebar loading with ordering
            if (!$this->indexExists('categories', 'categories_is_active_display_order_index')) {
                $table->index(['is_active', 'display_order']);
            }
        });

        // Tenants indexes (if table exists)
        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                // For foodcourt sidebar loading
                if (!$this->indexExists('tenants', 'tenants_is_active_display_order_index')) {
                    $table->index(['is_active', 'display_order']);
                }
            });
        }

        // Financial transactions indexes
        if (Schema::hasTable('financial_transactions')) {
            Schema::table('financial_transactions', function (Blueprint $table) {
                // For financial dashboard queries
                if (!$this->indexExists('financial_transactions', 'financial_transactions_status_created_at_index')) {
                    $table->index(['status', 'created_at']);
                }
                
                // For type-based filtering
                if (!$this->indexExists('financial_transactions', 'financial_transactions_type_status_index')) {
                    $table->index(['type', 'status']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['payment_method', 'status']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
            $table->dropIndex(['product_id', 'created_at']);
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['outlet_type', 'is_active']);
            $table->dropIndex(['outlet_type', 'is_active', 'stock_quantity']);
            $table->dropIndex(['name']);
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->dropIndex(['is_active', 'display_order']);
        });

        if (Schema::hasTable('tenants')) {
            Schema::table('tenants', function (Blueprint $table) {
                $table->dropIndex(['is_active', 'display_order']);
            });
        }

        if (Schema::hasTable('financial_transactions')) {
            Schema::table('financial_transactions', function (Blueprint $table) {
                $table->dropIndex(['status', 'created_at']);
                $table->dropIndex(['type', 'status']);
            });
        }
    }

    /**
     * Check if index already exists
     * 
     * @param string $table
     * @param string $index
     * @return bool
     */
    protected function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $indexes = $connection->getDoctrineSchemaManager()
            ->listTableIndexes($table);
        
        return array_key_exists($index, $indexes);
    }
};
