<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CarPart;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function createPaymentLink(Order $order)
    {
        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        $paymentLink = \Stripe\PaymentLink::create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => 'Order #' . $order->id,
                        ],
                        'unit_amount' => $order->total * 100,
                    ],
                    'quantity' => 1,
                ],
            ],
        ]);

        return response()->json(['payment_link' => $paymentLink->url]);
    }

    public function export()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="car_parts.csv"',
        ];

        $carParts = CarPart::all();

        $callback = function () use ($carParts) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Name', 'Category', 'Price', 'Stock Quantity']);

            foreach ($carParts as $carPart) {
                fputcsv($file, [$carPart->id, $carPart->name, $carPart->category, $carPart->price, $carPart->stock_quantity]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
