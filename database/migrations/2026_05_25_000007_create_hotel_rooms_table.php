<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->nullable()->constrained('hotel_room_types')->nullOnDelete();
            $table->string('room_number')->unique();
            $table->string('floor')->nullable();
            $table->enum('status', ['vacant_clean', 'occupied', 'reserved', 'dirty', 'out_of_order'])->default('vacant_clean');
            $table->enum('housekeeping_status', ['clean', 'dirty', 'inspected', 'out_of_order'])->default('clean');
            $table->string('active_guest_name')->nullable();
            $table->decimal('active_folio_balance', 12, 2)->default(0);
            $table->decimal('current_rate', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_rooms');
    }
};
