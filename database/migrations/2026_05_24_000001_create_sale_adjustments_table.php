<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sale_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->string('adjustment_type', 40);
            $table->string('status', 40)->default('approved');
            $table->decimal('amount', 14, 2)->default(0);
            $table->string('reason', 255);
            $table->text('notes')->nullable();
            $table->foreignId('actor_user_id')->constrained('users');
            $table->foreignId('approver_user_id')->nullable()->constrained('users');
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['sale_id', 'adjustment_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_adjustments');
    }
};
