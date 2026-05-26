<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pos_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action_type', 60);
            $table->foreignId('actor_user_id')->constrained('users');
            $table->foreignId('approver_user_id')->nullable()->constrained('users');
            $table->foreignId('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['action_type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_audit_logs');
    }
};
