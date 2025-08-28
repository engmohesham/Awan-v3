<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Course;
use App\Models\Payment;
use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    protected $paymentService;

    public function __construct(PaymentGatewayService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * إنشاء طلب جديد للدورة
     */
    public function createOrder(Request $request)
    {
        try {
            $request->validate([
                'course_id' => 'required|exists:courses,id',
                'notes' => 'nullable|string|max:500',
            ]);

            $course = Course::findOrFail($request->course_id);
            $user = Auth::user();

            // التحقق من أن المستخدم لم يشتري الدورة من قبل
            if ($user->orders()->where('course_id', $course->id)->where('status', Order::STATUS_CONFIRMED)->exists()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لقد قمت بشراء هذه الدورة من قبل'
                ], 400);
            }

            // التحقق من وجود طلب معلق
            $pendingOrder = $user->orders()
                ->where('course_id', $course->id)
                ->where('status', Order::STATUS_PENDING)
                ->where('payment_status', Order::PAYMENT_STATUS_PENDING)
                ->first();

            if ($pendingOrder) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'لديك طلب معلق بالفعل',
                    'data' => new OrderResource($pendingOrder)
                ]);
            }

            DB::beginTransaction();

            // إنشاء الطلب
            $order = Order::create([
                'user_id' => $user->id,
                'course_id' => $course->id,
                'order_number' => Order::generateOrderNumber(),
                'amount' => $course->price,
                'currency' => 'EGP',
                'status' => Order::STATUS_PENDING,
                'payment_status' => Order::PAYMENT_STATUS_PENDING,
                'notes' => $request->notes,
                'expires_at' => now()->addHours(config('payment.order.expiration_hours', 24)),
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم إنشاء الطلب بنجاح',
                'data' => new OrderResource($order)
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء إنشاء الطلب'
            ], 500);
        }
    }

    /**
     * عرض تفاصيل الطلب
     */
    public function showOrder($orderId)
    {
        try {
            $user = Auth::user();
            $order = $user->orders()->with(['course', 'payments'])->findOrFail($orderId);

            return response()->json([
                'status' => 'success',
                'data' => new OrderResource($order)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'الطلب غير موجود'
            ], 404);
        }
    }

    /**
     * عرض جميع طلبات المستخدم
     */
    public function userOrders(Request $request)
    {
        try {
            $user = Auth::user();
            $status = $request->get('status');
            $perPage = $request->get('per_page', 10);

            $query = $user->orders()->with(['course', 'payments']);

            if ($status) {
                $query->where('status', $status);
            }

            $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'status' => 'success',
                'data' => OrderResource::collection($orders),
                'pagination' => [
                    'current_page' => $orders->currentPage(),
                    'last_page' => $orders->lastPage(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء جلب الطلبات'
            ], 500);
        }
    }

    /**
     * إلغاء الطلب
     */
    public function cancelOrder($orderId)
    {
        try {
            $user = Auth::user();
            $order = $user->orders()->findOrFail($orderId);

            if (!$order->canBePaid()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لا يمكن إلغاء هذا الطلب'
                ], 400);
            }

            $order->markAsCancelled();

            return response()->json([
                'status' => 'success',
                'message' => 'تم إلغاء الطلب بنجاح',
                'data' => new OrderResource($order)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء إلغاء الطلب'
            ], 500);
        }
    }

    /**
     * إنشاء عملية دفع جديدة
     */
    public function createPayment(Request $request, $orderId)
    {
        try {
            $request->validate([
                'payment_method' => 'required|in:card,cash,bank_transfer,vodafone_cash',
                'payment_gateway' => 'nullable|string',
            ]);

            $user = Auth::user();
            $order = $user->orders()->findOrFail($orderId);

            if (!$order->canBePaid()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لا يمكن الدفع لهذا الطلب'
                ], 400);
            }

            DB::beginTransaction();

            // إنشاء عملية الدفع
            $payment = Payment::create([
                'order_id' => $order->id,
                'user_id' => $user->id,
                'amount' => $order->amount,
                'currency' => $order->currency,
                'payment_method' => $request->payment_method,
                'payment_gateway' => $request->payment_gateway,
                'status' => Payment::STATUS_PENDING,
            ]);

            // إذا كان الدفع نقدي أو تحويل بنكي، نحتاج لإثبات الدفع
            if (in_array($request->payment_method, [Payment::METHOD_CASH, Payment::METHOD_BANK_TRANSFER])) {
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'تم إنشاء عملية الدفع، يرجى رفع إثبات الدفع',
                    'data' => new PaymentResource($payment),
                    'requires_proof' => true
                ]);
            }

            // إذا كان الدفع ببطاقة ائتمان أو فودافون كاش، نوجه لبوابة الدفع
            if (in_array($request->payment_method, [Payment::METHOD_CARD, Payment::METHOD_VODAFONE_CASH])) {
                $paymentUrl = $this->paymentService->getPaymentUrl($payment);
                
                if ($paymentUrl) {
                    DB::commit();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'تم إنشاء عملية الدفع، سيتم توجيهك لبوابة الدفع',
                        'data' => new PaymentResource($payment),
                        'payment_url' => $paymentUrl
                    ]);
                } else {
                    DB::rollBack();
                    return response()->json([
                        'status' => 'error',
                        'message' => 'حدث خطأ أثناء إنشاء رابط الدفع'
                    ], 500);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم إنشاء عملية الدفع بنجاح',
                'data' => new PaymentResource($payment)
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء إنشاء عملية الدفع'
            ], 500);
        }
    }

    /**
     * رفع إثبات الدفع
     */
    public function uploadPaymentProof(Request $request, $orderId)
    {
        try {
            $request->validate([
                'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:' . config('payment.uploads.max_size', 2048),
                'sender_name' => 'required|string|max:255',
                'sender_phone' => 'required|string|max:20',
            ]);

            $user = Auth::user();
            $order = $user->orders()->findOrFail($orderId);
            $payment = $order->payments()->latest()->first();

            if (!$payment || $payment->status !== Payment::STATUS_PENDING) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لا توجد عملية دفع معلقة'
                ], 400);
            }

            // رفع الصورة
            $imagePath = $request->file('proof_image')->store(
                config('payment.uploads.path', 'payment-proofs'), 
                config('payment.uploads.disk', 'public')
            );

            // تحديث عملية الدفع
            $payment->update([
                'gateway_response' => [
                    'sender_name' => $request->sender_name,
                    'sender_phone' => $request->sender_phone,
                    'proof_image' => $imagePath,
                ]
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'تم رفع إثبات الدفع بنجاح، سيتم مراجعته من قبل الإدارة',
                'data' => new PaymentResource($payment)
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'خطأ في البيانات المدخلة',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء رفع إثبات الدفع'
            ], 500);
        }
    }

    /**
     * callback من بوابة الدفع
     */
    public function paymentCallback(Request $request)
    {
        try {
            $gateway = $request->get('gateway', 'paytabs');
            $verification = $this->paymentService->verifyCallback($gateway, $request);

            if (!$verification['success']) {
                return response()->json([
                    'status' => 'error',
                    'message' => $verification['message']
                ], 400);
            }

            $payment = $verification['payment'] ?? null;
            
            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'عملية دفع غير موجودة'
                ], 404);
            }

            DB::beginTransaction();

            // تحديث حالة الدفع حسب استجابة بوابة الدفع
            $data = $verification['data'] ?? [];
            $status = $data['response_status'] ?? $data['status'] ?? 'failed';

            if ($status === 'A' || $status === 'success' || $status === 'paid') {
                $payment->markAsPaid();
                $payment->order->markAsPaid();
                
                // إنشاء purchase record
                $this->createPurchaseRecord($payment->order);
            } else {
                $payment->markAsFailed($data['failure_reason'] ?? 'Payment failed');
                $payment->order->markAsFailed();
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'تم معالجة الدفع بنجاح'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء معالجة الدفع'
            ], 500);
        }
    }

    /**
     * إلغاء الدفع من بوابة الدفع
     */
    public function paymentCancel(Request $request)
    {
        try {
            $orderId = $request->get('order_id');
            $payment = Payment::where('gateway_order_id', $orderId)->first();

            if ($payment) {
                $payment->markAsCancelled();
                $payment->order->markAsCancelled();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'تم إلغاء عملية الدفع'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء إلغاء الدفع'
            ], 500);
        }
    }

    /**
     * إنشاء سجل الشراء بعد الدفع الناجح
     */
    private function createPurchaseRecord(Order $order)
    {
        // إنشاء سجل في جدول purchases
        \App\Models\Purchase::create([
            'user_id' => $order->user_id,
            'course_id' => $order->course_id,
            'amount' => $order->amount,
            'payment_method' => $order->payments()->latest()->first()->payment_method,
            'payment_status' => 'paid',
        ]);
    }
}
