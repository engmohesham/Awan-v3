<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // تحديث ENUM للـ status
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'expired', 'paid', 'failed') DEFAULT 'pending'");
        
        // تحديث ENUM للـ payment_status
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('pending', 'paid', 'failed', 'cancelled', 'refunded') DEFAULT 'pending'");
    }

    public function down()
    {
        // إرجاع ENUM للـ status
        DB::statement("ALTER TABLE orders MODIFY COLUMN status ENUM('pending', 'confirmed', 'cancelled', 'expired') DEFAULT 'pending'");
        
        // إرجاع ENUM للـ payment_status
        DB::statement("ALTER TABLE orders MODIFY COLUMN payment_status ENUM('pending', 'paid', 'failed', 'cancelled') DEFAULT 'pending'");
    }
};
