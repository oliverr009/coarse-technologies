<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_type_id')->nullable()->constrained('hotel_room_types')->nullOnDelete();
            $table->foreignId('hotel_room_id')->nullable()->constrained('hotel_rooms')->nullOnDelete();
            $table->string('guest_name');
            $table->string('guest_phone', 80)->nullable();
            $table->string('guest_email', 160)->nullable();
            $table->date('check_in_date');
            $table->date('check_out_date');
            $table->unsignedInteger('guests')->default(1);
            $table->string('rate_plan', 80)->default('rack');
            $table->decimal('nightly_rate', 12, 2)->default(0);
            $table->decimal('deposit_amount', 12, 2)->default(0);
            $table->string('status', 30)->default('confirmed');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['check_in_date', 'check_out_date', 'status']);
            $table->index(['hotel_room_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_reservations');
    }
};
