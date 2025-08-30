<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'status' => $this->status,
            'proof_image' => $this->proof_image ? Storage::url($this->proof_image) : null,
            'sender_name' => $this->sender_name,
            'sender_phone' => $this->sender_phone,
            'notes' => $this->notes,
            
            // Customer details
            'customer' => [
                'name' => $this->customer_name,
                'email' => $this->customer_email,
                'phone' => $this->customer_phone,
            ],
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            'failure_reason' => $this->failure_reason,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Computed fields
            'is_successful' => $this->isSuccessful(),
            'requires_proof' => true, // جميع طرق الدفع تحتاج إثبات
            
            // Order details
            'order' => $this->when($this->relationLoaded('order'), [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->order->status,
            ]),
        ];
    }
} 