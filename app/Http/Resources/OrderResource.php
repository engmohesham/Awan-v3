<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'notes' => $this->notes,
            'expires_at' => $this->expires_at?->format('Y-m-d H:i:s'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Course details
            'course' => [
                'id' => $this->course->id,
                'title' => $this->course->title,
                'slug' => $this->course->slug,
                'price' => $this->course->price,
                'description' => $this->course->description,
            ],
            
            // User details (only basic info)
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            
            // Payments
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'latest_payment' => new PaymentResource($this->whenLoaded('latestPayment')),
            
            // Computed fields
            'is_expired' => $this->isExpired(),
            'can_be_paid' => $this->canBePaid(),
            'time_remaining' => $this->expires_at ? now()->diffInSeconds($this->expires_at, false) : null,
            
            // Payment information
            'payment_info' => [
                'vodafone_cash' => [
                    'name' => 'Vodafone Cash',
                    'phone_number' => config('payment.methods.vodafone_cash.phone_number'),
                    'description' => config('payment.methods.vodafone_cash.description'),
                ],
                'instapay' => [
                    'name' => 'Instapay',
                    'username' => config('payment.methods.instapay.username'),
                    'description' => config('payment.methods.instapay.description'),
                ]
            ],
            'payment_instructions' => 'يرجى اختيار طريقة الدفع وإرسال إثبات الدفع مع رقم الهاتف المرسل',
        ];
    }
}

