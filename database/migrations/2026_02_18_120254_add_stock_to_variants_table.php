<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('variants', function (Blueprint $table) {
            // Kita tambahin kolom stock, default 0 biar gak error data lama
            $table->integer('stock')->default(0)->after('price_usd');
        });
    }

    public function down()
    {
        Schema::table('variants', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }
};