<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Http\Resources\PurchaseResource;

class PurchaseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Purchase::class, 'purchase');
    }

    public function index()
    {
        return PurchaseResource::collection(Purchase::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => 'required|exists:users,id',
            'course_id' => 'required|exists:courses,id',
            'status' => 'required|string',
            // Add other fields as needed
        ]);
        $purchase = Purchase::create($data);
        return new PurchaseResource($purchase);
    }

    public function show(Purchase $purchase)
    {
        return new PurchaseResource($purchase);
    }

    public function update(Request $request, Purchase $purchase)
    {
        $data = $request->validate([
            'status' => 'sometimes|required|string',
            // Add other fields as needed
        ]);
        $purchase->update($data);
        return new PurchaseResource($purchase);
    }

    public function destroy(Purchase $purchase)
    {
        $purchase->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }
} 