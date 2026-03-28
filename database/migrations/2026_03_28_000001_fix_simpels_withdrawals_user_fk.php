<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Fix foreign key constraints for user relations in simpels_withdrawals table
     * to allow NULL values and handle deleted users gracefully.
     */
    public function up(): void
    {
        // First, check if table exists
        if (!Schema::hasTable('simpels_withdrawals')) {
            \Log::warning('Table simpels_withdrawals does not exist, skipping migration');
            return;
        }

        // Set any orphaned user references to NULL or a system user
        $systemUser = DB::table('users')->where('email', 'system@epos.local')->first();
        
        if (!$systemUser) {
            // Create a system user if doesn't exist
            $systemUserId = DB::table('users')->insertGetId([
                'name' => 'System',
                'email' => 'system@epos.local',
                'password' => bcrypt('system-user-' . \Str::random(32)),
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            $systemUserId = $systemUser->id;
        }

        // Fix orphaned requested_by
        DB::table('simpels_withdrawals')
            ->whereNotNull('requested_by')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('users')
                      ->whereRaw('users.id = simpels_withdrawals.requested_by');
            })
            ->update(['requested_by' => $systemUserId]);

        // Fix orphaned approved_by (set to NULL since it's optional)
        DB::table('simpels_withdrawals')
            ->whereNotNull('approved_by')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                      ->from('users')
                      ->whereRaw('users.id = simpels_withdrawals.approved_by');
            })
            ->update(['approved_by' => null]);

        \Log::info('Fixed orphaned user references in simpels_withdrawals table');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse data fixes
    }
};
