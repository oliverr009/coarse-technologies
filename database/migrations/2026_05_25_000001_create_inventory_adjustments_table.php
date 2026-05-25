<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_adjustments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('outlet_id')->default(1);
            $table->decimal('expected_qty', 14, 4)->default(0);
            $table->decimal('counted_qty', 14, 4)->default(0);
            $table->decimal('variance_qty', 14, 4)->default(0);
            $table->decimal('unit_cost', 14, 2)->nullable();
            $table->string('reason', 60);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['product_id', 'created_at']);
            $table->index(['reason', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_adjustments');
    }
};
