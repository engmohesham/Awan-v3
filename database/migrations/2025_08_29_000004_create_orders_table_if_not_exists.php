<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('course_id')->constrained()->onDelete('cascade');
                $table->string('order_number')->unique();
                $table->decimal('amount', 10, 2);
                $table->string('currency', 3)->default('EGP');
                $table->enum('status', ['pending', 'confirmed', 'cancelled', 'expired'])->default('pending');
                $table->enum('payment_status', ['pending', 'paid', 'failed', 'cancelled'])->default('pending');
                $table->text('notes')->nullable();
                $table->timestamp('expires_at')->nullable();
                
                // تفاصيل العميل
                $table->string('customer_name')->nullable();
                $table->string('customer_email')->nullable();
                $table->string('customer_phone')->nullable();
                
                $table->timestamps();

                $table->index(['user_id', 'status']);
                $table->index(['order_number']);
                $table->index(['expires_at']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
