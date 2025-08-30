<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Course;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\OrderResource;
use App\Http\Resources\PaymentResource;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{

    /**
     * إنشاء طلب جديد للدورة
     */
    public function createOrder(Request $request)
    {
        try {
            $request->validate([
                'items' => 'required|array|min:1',
                'items.*.course_id' => 'required|exists:courses,id',
                'items.*.price' => 'required|numeric|min:0',
                'subtotal' => 'required|numeric|min:0',
                'total' => 'required|numeric|min:0',
                'transaction_fee' => 'required|numeric|min:0',
                'payment_method' => 'required|in:vodafone_cash,instapay',
                'user_info' => 'required|array',
                'user_info.email' => 'required|email',
                'user_info.first_name' => 'required|string|max:255',
                'user_info.last_name' => 'required|string|max:255',
                'user_info.phone' => 'required|string|max:20',
                'notes' => 'nullable|string|max:500',
            ]);

            $user = Auth::user();
            $courses = [];
            $totalAmount = 0;
            $orderItems = [];

            // التحقق من جميع الكورسات
            foreach ($request->items as $item) {
                $course = Course::findOrFail($item['course_id']);
                $courses[] = $course;
                $totalAmount += $item['price'];
                $orderItems[] = [
                    'course_id' => $course->id,
                    'price' => $item['price']
                ];

                // التحقق من أن المستخدم لم يشتري الدورة من قبل
                if ($user->orders()->where('course_id', $course->id)->where('status', Order::STATUS_CONFIRMED)->exists()) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "لقد قمت بشراء دورة '{$course->title}' من قبل"
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
                        'message' => "لديك طلب معلق لدورة '{$course->title}' بالفعل",
                        'data' => new OrderResource($pendingOrder)
                    ]);
                }
            }

            DB::beginTransaction();

            $createdOrders = [];

            // إنشاء order منفصل لكل كورس
            foreach ($orderItems as $item) {
                $order = Order::create([
                    'user_id' => $user->id,
                    'course_id' => $item['course_id'],
                    'order_number' => Order::generateOrderNumber(),
                    'amount' => $item['price'],
                    'currency' => 'EGP',
                    'status' => Order::STATUS_PENDING,
                    'payment_status' => Order::PAYMENT_STATUS_PENDING,
                    'notes' => $request->notes,
                    'expires_at' => now()->addHours(config('payment.order.expiration_hours', 24)),
                    // حفظ تفاصيل المستخدم من الـ request
                    'customer_name' => $request->user_info['first_name'] . ' ' . $request->user_info['last_name'],
                    'customer_email' => $request->user_info['email'],
                    'customer_phone' => $request->user_info['phone'],
                ]);

                $createdOrders[] = $order;
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => count($createdOrders) > 1 ? 
                    'تم إنشاء الطلبات بنجاح' : 'تم إنشاء الطلب بنجاح',
                'data' => OrderResource::collection($createdOrders),
                'total_amount' => $request->total,
                'courses_count' => count($createdOrders)
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
                'payment_method' => 'required|in:vodafone_cash,instapay',
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
                'status' => Payment::STATUS_PENDING,
                // حفظ تفاصيل المستخدم من الـ order
                'customer_name' => $order->customer_name,
                'customer_email' => $order->customer_email,
                'customer_phone' => $order->customer_phone,
            ]);

            // الحل النهائي: commit فوري للـ transaction
            DB::commit();

            // Debug: التحقق من إنشاء الـ payment
            $createdPayment = Payment::find($payment->id);
            $allUserPayments = Payment::where('user_id', $user->id)->get();

            // الحصول على معلومات الدفع
            $paymentInfo = $this->getPaymentInfo($request->payment_method);

            return response()->json([
                'status' => 'success',
                'message' => 'تم إنشاء عملية الدفع، يرجى رفع إثبات الدفع',
                'data' => new PaymentResource($payment),
                'payment_info' => $paymentInfo,
                'requires_proof' => true,
                'debug' => [
                    'payment_created_id' => $payment->id,
                    'payment_found_id' => $createdPayment ? $createdPayment->id : null,
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                    'all_user_payments_count' => $allUserPayments->count(),
                    'all_user_payments' => $allUserPayments->map(function($p) {
                        return [
                            'id' => $p->id,
                            'order_id' => $p->order_id,
                            'user_id' => $p->user_id,
                            'status' => $p->status,
                            'created_at' => $p->created_at
                        ];
                    })
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
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
     * الحصول على معلومات الدفع
     */
    private function getPaymentInfo($paymentMethod)
    {
        $config = config("payment.methods.{$paymentMethod}");
        
        if (!$config || !$config['enabled']) {
            return null;
        }

        return [
            'method_name' => $config['name'],
            'description' => $config['description'],
            'phone_number' => $config['phone_number'] ?? null,
            'username' => $config['username'] ?? null,
        ];
    }

    /**
     * الحصول على معلومات طرق الدفع المتاحة
     */
    public function getPaymentMethods()
    {
        try {
            $paymentMethods = [
                'vodafone_cash' => [
                    'name' => config('payment.methods.vodafone_cash.name'),
                    'description' => config('payment.methods.vodafone_cash.description'),
                    'phone_number' => config('payment.methods.vodafone_cash.phone_number'),
                    'enabled' => config('payment.methods.vodafone_cash.enabled'),
                ],
                'instapay' => [
                    'name' => config('payment.methods.instapay.name'),
                    'description' => config('payment.methods.instapay.description'),
                    'username' => config('payment.methods.instapay.username'),
                    'enabled' => config('payment.methods.instapay.enabled'),
                ]
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'تم جلب معلومات طرق الدفع بنجاح',
                'data' => $paymentMethods
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ أثناء جلب معلومات طرق الدفع'
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
                'proof_image' => 'required|file|mimes:jpeg,png,jpg,pdf|max:' . config('payment.uploads.max_size', 5 * 1024 * 1024),
                'sender_name' => 'required|string|max:255',
                'sender_phone' => 'required|string|max:20',
            ]);

            $user = Auth::user();
            $order = $user->orders()->findOrFail($orderId);
            
            // الحل النهائي: البحث عن payment معلق للـ order المحدد
            $payment = Payment::where('order_id', $orderId)
                ->where('user_id', $user->id)
                ->where('status', Payment::STATUS_PENDING)
                ->latest()
                ->first();

            // Debug: طباعة معلومات للبحث عن المشكلة
            $allPayments = Payment::where('user_id', $user->id)->get();
            $pendingPayments = Payment::where('user_id', $user->id)
                ->where('status', Payment::STATUS_PENDING)
                ->get();
            
            $orderPayments = Payment::where('order_id', $orderId)->get();
            $pendingOrderPayments = Payment::where('order_id', $orderId)
                ->where('status', Payment::STATUS_PENDING)
                ->get();

            // الحل النهائي: إذا لم يتم العثور على payment، ابحث عن أي payment معلق للـ user
            if (!$payment) {
                $payment = Payment::where('user_id', $user->id)
                    ->where('status', Payment::STATUS_PENDING)
                    ->latest()
                    ->first();
            }

            // Debug: طباعة معلومات إضافية
            $allPaymentsInDB = \DB::table('payments')->where('user_id', $user->id)->get();
            $pendingPaymentsInDB = \DB::table('payments')->where('user_id', $user->id)->where('status', 'pending')->get();

            if (!$payment) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'لا توجد عملية دفع معلقة',
                    'debug' => [
                        'user_id' => $user->id,
                        'order_id' => $orderId,
                        'all_payments_count' => $allPayments->count(),
                        'pending_payments_count' => $pendingPayments->count(),
                        'order_payments_count' => $orderPayments->count(),
                        'pending_order_payments_count' => $pendingOrderPayments->count(),
                        'all_payments_in_db_count' => $allPaymentsInDB->count(),
                        'pending_payments_in_db_count' => $pendingPaymentsInDB->count(),
                        'all_payments' => $allPayments->map(function($p) {
                            return [
                                'id' => $p->id,
                                'order_id' => $p->order_id,
                                'user_id' => $p->user_id,
                                'status' => $p->status,
                                'created_at' => $p->created_at
                            ];
                        }),
                        'pending_payments' => $pendingPayments->map(function($p) {
                            return [
                                'id' => $p->id,
                                'order_id' => $p->order_id,
                                'user_id' => $p->user_id,
                                'status' => $p->status,
                                'created_at' => $p->created_at
                            ];
                        }),
                        'order_payments' => $orderPayments->map(function($p) {
                            return [
                                'id' => $p->id,
                                'order_id' => $p->order_id,
                                'user_id' => $p->user_id,
                                'status' => $p->status,
                                'created_at' => $p->created_at
                            ];
                        }),
                        'all_payments_in_db' => $allPaymentsInDB->map(function($p) {
                            return [
                                'id' => $p->id,
                                'order_id' => $p->order_id,
                                'user_id' => $p->user_id,
                                'status' => $p->status,
                                'created_at' => $p->created_at
                            ];
                        })
                    ]
                ], 400);
            }

            // رفع الملف
            $filePath = $request->file('proof_image')->store(
                config('payment.uploads.path', 'payment-proofs'), 
                'public'
            );

            // تحديث عملية الدفع
            $payment->update([
                'proof_image' => $filePath,
                'sender_name' => $request->sender_name,
                'sender_phone' => $request->sender_phone,
                // تحديث تفاصيل المرسل
                'customer_name' => $request->sender_name,
                'customer_phone' => $request->sender_phone,
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

}
