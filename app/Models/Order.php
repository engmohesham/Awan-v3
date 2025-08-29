<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'order_number',
        'amount',
        'currency',
        'status',
        'payment_status',
        'notes',
        'expires_at',
        'customer_name',
        'customer_email',
        'customer_phone',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
    ];

    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_EXPIRED = 'expired';

    // Payment status constants
    const PAYMENT_STATUS_PENDING = 'pending';
    const PAYMENT_STATUS_PAID = 'paid';
    const PAYMENT_STATUS_FAILED = 'failed';
    const PAYMENT_STATUS_CANCELLED = 'cancelled';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): BelongsTo
    {
        return $this->belongsTo(Payment::class)->latest();
    }

    // Generate unique order number
    public static function generateOrderNumber(): string
    {
        do {
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(uniqid());
        } while (self::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    // Check if order is expired
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    // Check if order can be paid
    public function canBePaid(): bool
    {
        return $this->status === self::STATUS_PENDING && 
               $this->payment_status === self::PAYMENT_STATUS_PENDING &&
               !$this->isExpired();
    }

    // Mark order as paid
    public function markAsPaid(): void
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'payment_status' => self::PAYMENT_STATUS_PAID,
        ]);
    }

    // Mark order as failed
    public function markAsFailed(string $reason = null): void
    {
        $this->update([
            'payment_status' => self::PAYMENT_STATUS_FAILED,
        ]);
    }

    // Mark order as cancelled
    public function markAsCancelled(): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'payment_status' => self::PAYMENT_STATUS_CANCELLED,
        ]);
    }

    // Mark order as expired
    public function markAsExpired(): void
    {
        $this->update([
            'status' => self::STATUS_EXPIRED,
            'payment_status' => self::PAYMENT_STATUS_CANCELLED,
        ]);
    }
}

