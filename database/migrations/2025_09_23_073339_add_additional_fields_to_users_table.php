<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('nis')->nullable()->after('class'); // Nomor Induk Santri
            $table->string('nip')->nullable()->after('nis'); // Nomor Induk Pegawai (for guru)
            $table->string('subject')->nullable()->after('nip'); // Subject for guru
            $table->integer('experience')->nullable()->after('subject'); // Years of experience for guru
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nis', 'nip', 'subject', 'experience']);
        });
    }
};
