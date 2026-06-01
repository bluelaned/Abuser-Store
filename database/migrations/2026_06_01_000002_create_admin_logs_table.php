<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('admin_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action', 50); // created, updated, deleted, exported, toggled, truncated
            $table->string('entity_type', 100)->nullable(); // product, transaction, user, promo, voucher, announcement, review, static_page
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('description');
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            $table->index(['action', 'entity_type']);
            $table->index('created_at');
        });
    }
    public function down(): void { Schema::dropIfExists('admin_logs'); }
};
