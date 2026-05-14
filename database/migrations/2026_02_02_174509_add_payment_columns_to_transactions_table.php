<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('transactions', function (Blueprint $table) {
            // Tambahkan kolom jika belum ada
            if (!Schema::hasColumn('transactions', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('price');
            }
            if (!Schema::hasColumn('transactions', 'unique_code')) {
                $table->integer('unique_code')->nullable()->after('price');
            }
            // Hapus customer_phone jika ada (karena tidak dipakai lagi)
            if (Schema::hasColumn('transactions', 'customer_phone')) {
                $table->dropColumn('customer_phone');
            }
        });
    }

    public function down()
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'unique_code']);
        });
    }
};