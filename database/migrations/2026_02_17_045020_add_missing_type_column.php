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
        // Cek dulu biar gak error "Duplicate Column"
        if (!Schema::hasColumn('products', 'type')) {
            Schema::table('products', function (Blueprint $table) {
                // Tambahkan kolom type
                $table->enum('type', ['internal', 'external'])->default('external')->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
