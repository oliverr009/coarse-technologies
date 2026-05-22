<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('order_type', 30)->default('dine_in')->after('order_number');
            $table->foreignId('customer_id')->nullable()->after('restaurant_table_id')->constrained()->nullOnDelete();
            $table->decimal('subtotal', 12, 2)->default(0)->after('covers');
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->text('modifier_notes')->nullable()->after('notes');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->string('order_type', 30)->default('takeaway')->after('order_id');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('subtotal');
            $table->string('discount_type', 20)->default('fixed')->after('discount_amount');
            $table->decimal('discount_value', 12, 2)->default(0)->after('discount_type');
            $table->decimal('service_charge_amount', 12, 2)->default(0)->after('discount_value');
            $table->decimal('service_charge_rate', 6, 2)->default(0)->after('service_charge_amount');
            $table->decimal('amount_paid', 12, 2)->default(0)->after('total_amount');
            $table->decimal('balance_due', 12, 2)->default(0)->after('amount_paid');
            $table->text('notes')->nullable()->after('status');
        });

        Schema::table('sale_items', function (Blueprint $table) {
            $table->text('notes')->nullable()->after('line_total');
        });

        Schema::create('sale_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->cascadeOnDelete();
            $table->string('method', 30);
            $table->decimal('amount', 12, 2);
            $table->string('reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sale_payments');

        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('notes');
        });

        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn([
                'order_type',
                'discount_amount',
                'discount_type',
                'discount_value',
                'service_charge_amount',
                'service_charge_rate',
                'amount_paid',
                'balance_due',
                'notes',
            ]);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn('modifier_notes');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('customer_id');
            $table->dropColumn(['order_type', 'subtotal']);
        });
    }
};
