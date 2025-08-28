<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;

class ExpireOrders extends Command
{
    protected $signature = 'orders:expire';
    protected $description = 'Expire orders that have passed their expiration time';

    public function handle()
    {
        $expiredOrders = Order::where('status', Order::STATUS_PENDING)
            ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
            ->where('expires_at', '<', now())
            ->get();

        $count = 0;
        foreach ($expiredOrders as $order) {
            $order->markAsExpired();
            $count++;
        }

        $this->info("Expired {$count} orders successfully.");
    }
}

