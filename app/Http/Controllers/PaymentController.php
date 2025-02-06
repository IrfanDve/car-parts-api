<?php
namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    // Create a payment link for an order
    public function createPaymentLink(Order $order)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // Calculate the total price of the order from order_items
        $totalPrice = $order->items->sum('total_price');

        // Create a Stripe Checkout Session
        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Order #' . $order->id,
                        ],
                        'unit_amount' => $totalPrice * 100, // Amount in cents
                    ],
                    'quantity' => 1,
                ],
            ],
            'mode' => 'payment',
            'success_url' => url('/payment/success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => url('/payment/cancel'),
        ]);

        // Create a payment record in the payments table
        Payment::create([
            'order_id' => $order->id,
            'amount' => $totalPrice,
            'transaction_id' => $session->payment_intent,
            'payment_method' => 'card', // Default value, can be updated later
            'status' => 'pending',
        ]);

        return response()->json([
            'payment_link' => $session->url,
        ]);
    }

    // Validate payment and update payment status
    public function validatePayment(Request $request, Order $order)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        // Retrieve the Stripe session
        $session = Session::retrieve($order->payments()->latest()->first()->transaction_id);

        // Check if the payment was successful
        if ($session->payment_status === 'paid') {
            // Update the payment status to "completed"
            $payment = $order->payments()->latest()->first();
            $payment->update([
                'status' => 'completed',
                'transaction_id' => $session->payment_intent,
                'payment_method' => $session->payment_method_types[0], // e.g., card
            ]);

            // Update the order status to "completed"
            $order->update(['status' => 'completed']);

            return response()->json([
                'message' => 'Payment verified and order status updated to completed.',
                'payment_details' => [
                    'amount' => $payment->amount,
                    'transaction_id' => $payment->transaction_id,
                    'payment_method' => $payment->payment_method,
                ],
            ]);
        }

        return response()->json([
            'message' => 'Payment not yet completed.',
        ], 400);
    }
}