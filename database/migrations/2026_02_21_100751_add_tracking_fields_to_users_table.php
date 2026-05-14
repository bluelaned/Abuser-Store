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
            $table->string('last_ip')->nullable();
            $table->string('location_city')->nullable();
            $table->string('location_state')->nullable();
            $table->string('location_country')->nullable();
            $table->string('location_flag')->nullable();
            $table->timestamp('last_seen')->nullable(); // Buat ngecek dia lagi online/enggak
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['last_ip', 'location_city', 'location_state', 'location_country', 'location_flag', 'last_seen']);
        });
    }
};
