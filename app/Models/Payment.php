<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'user_id',
        'amount',
        'currency',
        'payment_method',
        'status',
        'proof_image',
        'sender_phone',
        'sender_name',
        'notes',
        'paid_at',
        'failure_reason',
        'customer_name',
        'customer_email',
        'customer_phone',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PAID = 'paid';
    const STATUS_FAILED = 'failed';
    const STATUS_CANCELLED = 'cancelled';

    // Payment methods
    const METHOD_VODAFONE_CASH = 'vodafone_cash';
    const METHOD_INSTAPAY = 'instapay';

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Check if payment is successful
    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    // Mark payment as paid
    public function markAsPaid(): void
    {
        $this->update([
            'status' => self::STATUS_PAID,
            'paid_at' => now(),
        ]);
    }

    // Mark payment as failed
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'failure_reason' => $reason,
        ]);
    }

    // Mark payment as cancelled
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
        ]);
    }
} 