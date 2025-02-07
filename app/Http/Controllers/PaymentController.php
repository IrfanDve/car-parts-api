<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PaymentController extends Controller
{
    /**
     * Create a payment link for an order.
     *
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function createPaymentLink(Order $order)
    {
        try {
            $this->validateOrderForPayment($order);

            $this->configureStripe();

            return DB::transaction(function () use ($order) {
                $totalAmount = $this->calculateOrderTotal($order);
                
                $session = $this->createStripeSession($order, $totalAmount);
                
                $payment = $this->createPaymentRecord($order, $session, $totalAmount);

                Log::info('Payment link created', [
                    'order_id' => $order->id,
                    'payment_id' => $payment->id,
                    'amount' => $totalAmount
                ]);

                return $this->successResponse([
                    'payment_link' => $session->url,
                    'payment_id' => $payment->id,
                    'expires_at' => date('c', $session->expires_at)
                ], 'Payment link created successfully');
            });

        } catch (\Exception $e) {
            Log::error('Payment link creation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse(
                'Payment link creation failed: ' . $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    /**
     * Validate payment and update payment status.
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function validatePayment(Request $request, Order $order)
    {
        try {
            $this->configureStripe();

            return DB::transaction(function () use ($order) {
                $payment = $order->payments()->latest()->firstOrFail();
                
                $session = Session::retrieve($payment->transaction_id);

                if ($session->payment_status !== 'paid') {
                    return $this->errorResponse('Payment not completed', 400);
                }

                $this->updatePaymentDetails($payment, $session);
                $this->updateOrderStatus($order);

                Log::info('Payment validated successfully', [
                    'payment_id' => $payment->id,
                    'order_id' => $order->id
                ]);

                return $this->successResponse([
                    'amount' => $payment->amount,
                    'currency' => $payment->currency,
                    'payment_method' => $payment->payment_method,
                    'transaction_id' => $payment->transaction_id
                ], 'Payment verified successfully');

            });
        } catch (ModelNotFoundException $e) {
            return $this->errorResponse('No payment found for this order', 404);
        } catch (\Exception $e) {
            Log::error('Payment validation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            
            return $this->errorResponse(
                'Payment validation failed: ' . $e->getMessage(),
                $e->getCode() ?: 500
            );
        }
    }

    // --------------------------
    // Private Helper Methods
    // --------------------------

    private function configureStripe(): void
    {
        if (!config('services.stripe.secret')) {
            throw new \RuntimeException('Stripe configuration missing', 500);
        }
        
        Stripe::setApiKey(config('services.stripe.secret'));
        Stripe::setApiVersion(config('services.stripe.version', '2023-08-16'));
    }

    private function validateOrderForPayment(Order $order): void
    {
        if ($order->status === 'completed') {
            throw new \InvalidArgumentException('Order already completed', 400);
        }

        if ($order->items->isEmpty()) {
            throw new \InvalidArgumentException('Order has no items', 400);
        }
    }

    private function calculateOrderTotal(Order $order): float
    {
        return (float) $order->items->sum('total_price');
    }

    private function createStripeSession(Order $order, float $amount): Session
    {
        return Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => config('services.stripe.currency', 'usd'),
                    'product_data' => [
                        'name' => 'Order #' . $order->id,
                        'metadata' => ['order_id' => $order->id]
                    ],
                    'unit_amount' => (int) round($amount * 100),
                ],
                'quantity' => 1,
            ]],
            'mode' => 'payment',
            // 'success_url' => route('payment.success', ['order' => $order->id]),
            'success_url' => url("/payment/success?order={$order->id}"),
            // 'cancel_url' => route('payment.cancel', ['order' => $order->id]),
            'cancel_url' => url("/payment/cancel?order={$order->id}"),
            'client_reference_id' => $order->id,
            'metadata' => ['order_id' => $order->id],
            'expires_at' => now()->addHours(24)->timestamp,
        ]);
    }

    private function createPaymentRecord(Order $order, Session $session, float $amount): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'amount' => $amount,
            'currency' => config('services.stripe.currency', 'usd'),
            'transaction_id' => $session->payment_intent,
            'payment_method' => 'card', 
            'status' => 'pending',
            'metadata' => [
                'stripe_session_id' => $session->id,
                'checkout_url' => $session->url
            ]
        ]);
    }

    private function updatePaymentDetails(Payment $payment, Session $session): void
    {
        $payment->update([
            'status' => 'completed',
            'payment_method' => $session->payment_method_types[0],
            'transaction_id' => $session->payment_intent,
            'metadata' => array_merge(
                $payment->metadata,
                ['payment_intent' => $session->payment_intent]
            )
        ]);
    }

    private function updateOrderStatus(Order $order): void
    {
        $order->update(['status' => 'completed']);
        // Consider firing an OrderCompleted event here
    }

    private function successResponse(array $data = [], string $message = ''): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ]);
    }

    private function errorResponse(string $message, int $statusCode): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'status' => false,
            'error' => [
                'code' => $statusCode,
                'message' => $message
            ]
        ], $statusCode);
    }
}

