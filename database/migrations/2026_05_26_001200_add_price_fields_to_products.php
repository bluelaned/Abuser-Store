<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('products', 'official_price')) {
            Schema::table('products', function (Blueprint $table) {
                // Cost price (from official supplier)
                $table->decimal('official_price', 12, 4)->nullable()->after('delivery_method');
                // Selling price shown to customers
                $table->decimal('selling_price', 12, 4)->nullable()->after('official_price');
                // Currency for profit prices (default USD)
                $table->string('profit_currency', 10)->default('USD')->after('selling_price');
            });
        }
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['official_price', 'selling_price', 'profit_currency']);
        });
    }
};
