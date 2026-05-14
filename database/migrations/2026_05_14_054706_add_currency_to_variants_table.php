<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            // Add currency column with USD as default (preserves existing data)
            $table->string('currency', 10)->default('USD')->after('price_usd');
            // Make price a decimal that can hold any currency value
            $table->decimal('price_amount', 15, 4)->default(0)->after('currency');
        });

        // Migrate existing data: copy price_usd → price_amount, set currency = USD
        DB::statement("UPDATE variants SET price_amount = price_usd, currency = 'USD' WHERE price_usd IS NOT NULL AND price_usd > 0");
        DB::statement("UPDATE variants SET price_amount = price / 15500.0, currency = 'USD' WHERE (price_usd IS NULL OR price_usd = 0) AND price > 0");
    }

    public function down(): void
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->dropColumn(['currency', 'price_amount']);
        });
    }
};
