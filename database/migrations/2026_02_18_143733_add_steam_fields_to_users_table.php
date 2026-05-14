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
        Schema::table('users', function (Blueprint $table) {
            // Kita cuma bikin provider_id dan provider_name
            // Karena avatar katanya sudah ada di database lo
            $table->string('provider_id')->nullable()->after('id');
            $table->string('provider_name')->nullable()->after('provider_id');
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provider_id', 'provider_name']);
        });
    }
};
