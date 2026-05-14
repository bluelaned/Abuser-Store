<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            
            // Data Identitas Transaksi
            $table->string('reference')->unique(); // Ref dari Tripay (T00...)
            $table->string('merchant_ref'); // Invoice Kita (INV-...)
            
            // Data Produk
            $table->string('product_name');
            // Kita set nullable agar tidak error kalau varian dihapus
            $table->foreignId('variant_id')->nullable()->constrained('variants')->onDelete('set null'); 
            
            // Data Harga & Customer
            $table->integer('price'); // Harga Bayar (Final)
            $table->integer('original_price'); // Harga Asli
            $table->string('promo_code')->nullable();
            $table->string('customer_email');
            
            // Status Pembayaran
            $table->string('status')->default('UNPAID'); // UNPAID, PAID, FAILED
            $table->string('checkout_url')->nullable(); // Link Bayar
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};