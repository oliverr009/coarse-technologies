<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_folios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_reservation_id')->nullable()->constrained('hotel_reservations')->nullOnDelete();
            $table->foreignId('hotel_room_id')->constrained('hotel_rooms')->cascadeOnDelete();
            $table->string('guest_name');
            $table->string('guest_phone', 80)->nullable();
            $table->dateTime('checked_in_at');
            $table->dateTime('expected_checkout_at');
            $table->dateTime('checked_out_at')->nullable();
            $table->decimal('room_rate', 12, 2)->default(0);
            $table->decimal('room_charges', 12, 2)->default(0);
            $table->decimal('service_charge', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('payments', 12, 2)->default(0);
            $table->decimal('balance', 12, 2)->default(0);
            $table->string('status', 30)->default('open');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('closed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'expected_checkout_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_folios');
    }
};
