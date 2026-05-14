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
            $table->string('steam_id')->nullable()->unique();
            $table->string('avatar')->nullable();
            $table->string('email')->nullable()->change(); // Email boleh kosong dulu
            $table->string('password')->nullable()->change(); // Password boleh kosong
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
