<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Cek dulu, apakah kolom 'checkout_url' SUDAH ADA?
        if (!Schema::hasColumn('transactions', 'checkout_url')) {
            // Kalau BELUM ada, baru kita buat
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('checkout_url')->nullable()->after('status');
            });
        }
    }   

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            //
        });
    }
};
