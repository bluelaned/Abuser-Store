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
        Schema::create('promos', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Kode unik (misal: DISC10)
            $table->string('type'); // Jenis: 'percent' (persen) atau 'fixed' (potongan harga langsung)
            $table->integer('value'); // Nilainya (misal: 10 untuk 10%, atau 5000 untuk Rp 5.000)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promos');
    }
};
