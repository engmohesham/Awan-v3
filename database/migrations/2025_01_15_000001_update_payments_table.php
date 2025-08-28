<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop old columns if they exist
            if (Schema::hasColumn('payments', 'purchase_id')) {
                $table->dropForeign(['purchase_id']);
                $table->dropColumn('purchase_id');
            }

            // Add new columns
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EGP');
            $table->enum('payment_method', ['card', 'cash', 'bank_transfer', 'vodafone_cash'])->nullable();
            $table->string('payment_gateway')->nullable();
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_order_id')->nullable();
            $table->json('gateway_response')->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->text('failure_reason')->nullable();

            // Add indexes
            $table->index(['order_id']);
            $table->index(['user_id', 'status']);
            $table->index(['gateway_transaction_id']);
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['order_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn([
                'order_id', 'user_id', 'amount', 'currency', 'payment_method',
                'payment_gateway', 'gateway_transaction_id', 'gateway_order_id',
                'gateway_response', 'status', 'paid_at', 'failure_reason'
            ]);
            
            // Restore old structure
            $table->foreignId('purchase_id')->constrained()->onDelete('cascade');
        });
    }
};
