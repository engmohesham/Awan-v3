<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('EGP');
            $table->enum('payment_method', ['vodafone_cash', 'instapay'])->nullable();
            $table->enum('status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
            $table->string('proof_image')->nullable(); // صورة إثبات الدفع
            $table->string('sender_phone')->nullable(); // رقم الهاتف المرسل
            $table->string('sender_name')->nullable(); // اسم المرسل
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->text('failure_reason')->nullable();
            
            // تفاصيل العميل
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();
            
            $table->timestamps();

            $table->index(['order_id']);
            $table->index(['user_id', 'status']);
            $table->index(['payment_method']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
