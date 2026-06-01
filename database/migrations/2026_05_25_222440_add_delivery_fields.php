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
        if (!Schema::hasColumn('products', 'delivery_method')) {
            Schema::table('products', function (Blueprint $table) {
                // 'serial' = delivery via auto voucher keys, 'gift' = delivery via manual admin gift
                $table->string('delivery_method')->default('serial')->after('type');
            });
        }
        
        if (!Schema::hasColumn('transactions', 'gift_username')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->string('gift_username')->nullable()->after('promo_code');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('products', 'delivery_method')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('delivery_method');
            });
        }

        if (Schema::hasColumn('transactions', 'gift_username')) {
            Schema::table('transactions', function (Blueprint $table) {
                $table->dropColumn('gift_username');
            });
        }
    }
};
