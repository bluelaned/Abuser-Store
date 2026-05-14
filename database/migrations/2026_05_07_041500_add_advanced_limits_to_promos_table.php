<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('promos', function (Blueprint $table) {
            $table->decimal('max_discount', 15, 2)->default(0)->after('value');
            $table->integer('usage_limit_per_user')->default(0)->after('max_discount');
            $table->integer('min_qty')->default(1)->after('usage_limit_per_user');
            $table->unsignedBigInteger('product_id')->nullable()->after('min_qty');
        });
    }

    public function down()
    {
        Schema::table('promos', function (Blueprint $table) {
            $table->dropColumn(['max_discount', 'usage_limit_per_user', 'min_qty', 'product_id']);
        });
    }
};
