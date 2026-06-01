<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('static_pages', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 50)->unique(); // tos, privacy
            $table->string('title', 255);
            $table->longText('content');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('static_pages'); }
};
