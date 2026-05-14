<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Kita buat tabel BARU yang strukturnya langsung BENAR (pakai variant_id)
        Schema::create('voucher_codes', function (Blueprint $table) {
            $table->id();
            
            // Langsung kaitkan ke variant (bukan produk)
            $table->foreignId('variant_id')->constrained('variants')->onDelete('cascade');
            
            $table->string('code')->unique();
            $table->enum('status', ['AVAILABLE', 'SOLD'])->default('AVAILABLE');
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('voucher_codes');
    }
};