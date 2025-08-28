<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'payment_method' => $this->payment_method,
            'payment_gateway' => $this->payment_gateway,
            'gateway_transaction_id' => $this->gateway_transaction_id,
            'gateway_order_id' => $this->gateway_order_id,
            'status' => $this->status,
            'paid_at' => $this->paid_at?->format('Y-m-d H:i:s'),
            'failure_reason' => $this->failure_reason,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
            
            // Gateway response (only if needed)
            'gateway_response' => $this->when($request->user()?->hasRole('admin'), $this->gateway_response),
            
            // Computed fields
            'is_successful' => $this->isSuccessful(),
            'requires_proof' => in_array($this->payment_method, ['cash', 'bank_transfer']),
            
            // Order details
            'order' => [
                'id' => $this->order->id,
                'order_number' => $this->order->order_number,
                'status' => $this->order->status,
            ] when $this->relationLoaded('order'),
        ];
    }
} 