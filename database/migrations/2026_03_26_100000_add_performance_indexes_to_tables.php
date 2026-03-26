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
        try {
            Schema::table('transactions', function (Blueprint $table) {
                // For date-based filtering (used in dashboard & reports)
                $table->index('created_at', 'transactions_created_at_index');
                // For status-based queries with date (dashboard, statistics)
                $table->index(['status', 'created_at'], 'transactions_status_created_at_index');
                // For payment method statistics
                $table->index(['payment_method', 'status'], 'transactions_payment_method_status_index');
                // For cashier performance queries
                $table->index('user_id', 'transactions_user_id_index');
            });
        } catch (\Exception $e) {
            // Index might already exist, skip
        }

        // Transaction items indexes
        try {
            Schema::table('transaction_items', function (Blueprint $table) {
                // For date-based reporting
                $table->index('created_at', 'transaction_items_created_at_index');
                // For product sales report
                $table->index(['product_id', 'created_at'], 'transaction_items_product_id_created_at_index');
            });
        } catch (\Exception $e) {
            // Index might already exist, skip
        }

        // Products indexes for faster POS loading
        try {
            Schema::table('products', function (Blueprint $table) {
                // For outlet mode filtering (store/foodcourt)
                $table->index(['outlet_type', 'is_active'], 'products_outlet_type_is_active_index');
                // For stock-aware product loading
                $table->index(['outlet_type', 'is_active', 'stock_quantity'], 'products_outlet_is_active_stock_index');
                // For product search by name
                $table->index('name', 'products_name_index');
            });
        } catch (\Exception $e) {
            // Index might already exist, skip
        }

        // Categories indexes
        try {
            Schema::table('categories', function (Blueprint $table) {
                // For sidebar loading with ordering
                $table->index(['is_active', 'display_order'], 'categories_is_active_display_order_index');
            });
        } catch (\Exception $e) {
            // Index might already exist, skip
        }

        // Tenants indexes (if table exists)
        if (Schema::hasTable('tenants')) {
            try {
                Schema::table('tenants', function (Blueprint $table) {
                    // For foodcourt sidebar loading
                    $table->index(['is_active', 'display_order'], 'tenants_is_active_display_order_index');
                });
            } catch (\Exception $e) {
                // Index might already exist, skip
            }
        }

        // Financial transactions indexes
        if (Schema::hasTable('financial_transactions')) {
            try {
                Schema::table('financial_transactions', function (Blueprint $table) {
                    // For financial dashboard queries
                    $table->index(['status', 'created_at'], 'financial_transactions_status_created_at_index');
                    // For type-based filtering
                    $table->index(['type', 'status'], 'financial_transactions_type_status_index');
                });
            } catch (\Exception $e) {
                // Index might already exist, skip
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropIndex('transactions_created_at_index');
                $table->dropIndex('transactions_status_created_at_index');
                $table->dropIndex('transactions_payment_method_status_index');
                $table->dropIndex('transactions_user_id_index');
            });
        } catch (\Exception $e) {
            // Index might not exist, skip
        }

        try {
            Schema::table('transaction_items', function (Blueprint $table) {
                $table->dropIndex('transaction_items_created_at_index');
                $table->dropIndex('transaction_items_product_id_created_at_index');
            });
        } catch (\Exception $e) {
            // Index might not exist, skip
        }

        try {
            Schema::table('products', function (Blueprint $table) {
                $table->dropIndex('products_outlet_type_is_active_index');
                $table->dropIndex('products_outlet_is_active_stock_index');
                $table->dropIndex('products_name_index');
            });
        } catch (\Exception $e) {
            // Index might not exist, skip
        }

        try {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropIndex('categories_is_active_display_order_index');
            });
        } catch (\Exception $e) {
            // Index might not exist, skip
        }

        if (Schema::hasTable('tenants')) {
            try {
                Schema::table('tenants', function (Blueprint $table) {
                    $table->dropIndex('tenants_is_active_display_order_index');
                });
            } catch (\Exception $e) {
                // Index might not exist, skip
            }
        }

        if (Schema::hasTable('financial_transactions')) {
            try {
                Schema::table('financial_transactions', function (Blueprint $table) {
                    $table->dropIndex('financial_transactions_status_created_at_index');
                    $table->dropIndex('financial_transactions_type_status_index');
                });
            } catch (\Exception $e) {
                // Index might not exist, skip
            }
        }
    }
};
