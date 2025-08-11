<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use App\Http\Resources\PaymentResource;

class PaymentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Payment::class, 'payment');
    }

    public function index()
    {
        return PaymentResource::collection(Payment::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'purchase_id' => 'required|exists:purchases,id',
            'status' => 'required|string',
            // Add other fields as needed
        ]);
        $payment = Payment::create($data);
        return new PaymentResource($payment);
    }

    public function show(Payment $payment)
    {
        return new PaymentResource($payment);
    }

    public function update(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'status' => 'sometimes|required|string',
            // Add other fields as needed
        ]);
        $payment->update($data);
        return new PaymentResource($payment);
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
} 