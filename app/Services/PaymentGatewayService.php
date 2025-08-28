<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('payment');
    }

    /**
     * إنشاء رابط الدفع لبوابة PayTabs
     */
    public function createPayTabsPayment(Payment $payment)
    {
        $gatewayConfig = $this->config['gateways']['paytabs'];
        
        $payload = [
            'profile_id' => $gatewayConfig['profile_id'],
            'tran_type' => 'sale',
            'tran_class' => 'ecom',
            'cart_amount' => $payment->amount,
            'cart_currency' => $payment->currency,
            'cart_id' => $payment->order->order_number,
            'cart_description' => 'شراء دورة: ' . $payment->order->course->title,
            'paypage_lang' => $gatewayConfig['language'],
            'customer_details' => [
                'name' => $payment->user->name,
                'email' => $payment->user->email,
                'phone' => $payment->user->phone ?? '',
                'street1' => '',
                'city' => '',
                'state' => '',
                'country' => 'EG',
                'zip' => ''
            ],
            'return' => [
                'url' => route('payment.callback')
            ],
            'callback' => [
                'url' => route('payment.callback')
            ]
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $gatewayConfig['server_key'],
                'Content-Type' => 'application/json'
            ])->post($gatewayConfig['base_url'] . '/payment/request', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['response_code'] === '4012') {
                    // تحديث Payment مع معرف المعاملة
                    $payment->update([
                        'gateway_transaction_id' => $data['transaction_id'],
                        'gateway_order_id' => $data['cart_id'],
                        'gateway_response' => $data
                    ]);

                    return $data['redirect_url'];
                }
            }

            Log::error('PayTabs payment creation failed', [
                'payment_id' => $payment->id,
                'response' => $response->json()
            ]);

            return null;

        } catch (\Exception $e) {
            Log::error('PayTabs API error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * التحقق من صحة callback من PayTabs
     */
    public function verifyPayTabsCallback($request)
    {
        $gatewayConfig = $this->config['gateways']['paytabs'];
        
        $payload = [
            'profile_id' => $gatewayConfig['profile_id'],
            'tran_ref' => $request->get('tran_ref')
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => $gatewayConfig['server_key'],
                'Content-Type' => 'application/json'
            ])->post($gatewayConfig['base_url'] . '/payment/query', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['response_code'] === '4000') {
                    return [
                        'success' => true,
                        'data' => $data,
                        'payment' => Payment::where('gateway_transaction_id', $data['tran_ref'])->first()
                    ];
                }
            }

            return ['success' => false, 'message' => 'Invalid response'];

        } catch (\Exception $e) {
            Log::error('PayTabs verification error', [
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'message' => 'Verification failed'];
        }
    }

    /**
     * إنشاء رابط الدفع لـ Fawry
     */
    public function createFawryPayment(Payment $payment)
    {
        $gatewayConfig = $this->config['gateways']['fawry'];
        
        $items = [
            [
                'itemId' => $payment->order->course_id,
                'description' => $payment->order->course->title,
                'price' => $payment->amount,
                'quantity' => 1
            ]
        ];

        $signature = $this->generateFawrySignature($payment, $items, $gatewayConfig);

        $payload = [
            'merchantCode' => $gatewayConfig['merchant_code'],
            'merchantRefNum' => $payment->order->order_number,
            'customerProfileId' => $payment->user_id,
            'customerName' => $payment->user->name,
            'customerMobile' => $payment->user->phone ?? '',
            'customerEmail' => $payment->user->email,
            'amount' => $payment->amount,
            'currencyCode' => $payment->currency,
            'language' => 'ar-eg',
            'chargeItems' => $items,
            'signature' => $signature,
            'returnUrl' => route('payment.callback'),
            'paymentMethod' => 'CARD'
        ];

        try {
            $response = Http::post($gatewayConfig['base_url'] . '/ECommercePlugin/FawryPayment.aspx', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['statusCode'] === 200) {
                    $payment->update([
                        'gateway_order_id' => $data['referenceNumber'],
                        'gateway_response' => $data
                    ]);

                    return $data['paymentUrl'];
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Fawry payment creation error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * إنشاء رابط الدفع لـ Vodafone Cash
     */
    public function createVodafoneCashPayment(Payment $payment)
    {
        $gatewayConfig = $this->config['gateways']['vodafone_cash'];
        
        $payload = [
            'merchantId' => $gatewayConfig['merchant_id'],
            'amount' => $payment->amount,
            'currency' => $payment->currency,
            'orderId' => $payment->order->order_number,
            'description' => 'شراء دورة: ' . $payment->order->course->title,
            'customerPhone' => $payment->user->phone ?? '',
            'customerEmail' => $payment->user->email,
            'returnUrl' => route('payment.callback'),
            'cancelUrl' => route('payment.cancel')
        ];

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $gatewayConfig['api_key'],
                'Content-Type' => 'application/json'
            ])->post($gatewayConfig['base_url'] . '/payment/initiate', $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if ($data['status'] === 'success') {
                    $payment->update([
                        'gateway_transaction_id' => $data['transactionId'],
                        'gateway_order_id' => $data['orderId'],
                        'gateway_response' => $data
                    ]);

                    return $data['paymentUrl'];
                }
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Vodafone Cash payment creation error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * إنشاء Stripe Payment Intent
     */
    public function createStripePayment(Payment $payment)
    {
        $gatewayConfig = $this->config['gateways']['stripe'];
        
        \Stripe\Stripe::setApiKey($gatewayConfig['secret_key']);

        try {
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $payment->amount * 100, // Stripe uses cents
                'currency' => strtolower($payment->currency),
                'metadata' => [
                    'order_id' => $payment->order->order_number,
                    'course_id' => $payment->order->course_id,
                    'user_id' => $payment->user_id
                ]
            ]);

            $payment->update([
                'gateway_transaction_id' => $paymentIntent->id,
                'gateway_response' => $paymentIntent->toArray()
            ]);

            return [
                'client_secret' => $paymentIntent->client_secret,
                'publishable_key' => $gatewayConfig['publishable_key']
            ];

        } catch (\Exception $e) {
            Log::error('Stripe payment creation error', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * التحقق من webhook من Stripe
     */
    public function verifyStripeWebhook($request)
    {
        $gatewayConfig = $this->config['gateways']['stripe'];
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sigHeader, $gatewayConfig['webhook_secret']
            );

            return [
                'success' => true,
                'event' => $event
            ];

        } catch (\Exception $e) {
            Log::error('Stripe webhook verification error', [
                'error' => $e->getMessage()
            ]);

            return ['success' => false, 'message' => 'Webhook verification failed'];
        }
    }

    /**
     * إنشاء توقيع Fawry
     */
    private function generateFawrySignature(Payment $payment, $items, $config)
    {
        $signatureString = $config['merchant_code'] . 
                          $payment->order->order_number . 
                          $payment->user_id . 
                          $payment->user->name . 
                          $payment->user->phone . 
                          $payment->user->email . 
                          $payment->amount . 
                          $payment->currency . 
                          json_encode($items);

        return hash_hmac('sha256', $signatureString, $config['security_key']);
    }

    /**
     * الحصول على رابط الدفع حسب طريقة الدفع
     */
    public function getPaymentUrl(Payment $payment)
    {
        switch ($payment->payment_gateway) {
            case 'paytabs':
                return $this->createPayTabsPayment($payment);
            
            case 'fawry':
                return $this->createFawryPayment($payment);
            
            case 'vodafone_cash':
                return $this->createVodafoneCashPayment($payment);
            
            case 'stripe':
                return $this->createStripePayment($payment);
            
            default:
                return null;
        }
    }

    /**
     * التحقق من callback حسب بوابة الدفع
     */
    public function verifyCallback($gateway, $request)
    {
        switch ($gateway) {
            case 'paytabs':
                return $this->verifyPayTabsCallback($request);
            
            case 'stripe':
                return $this->verifyStripeWebhook($request);
            
            default:
                return ['success' => false, 'message' => 'Unsupported gateway'];
        }
    }
}

