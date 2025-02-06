<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CarPart;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    // Place a new order
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.car_part_id' => 'required|exists:car_parts,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Calculate total price and check stock
        $totalPrice = 0;
        $items = [];

        foreach ($request->items as $item) {
            $carPart = CarPart::find($item['car_part_id']);

            if ($carPart->stock_quantity < $item['quantity']) {
                throw ValidationException::withMessages([
                    'items' => ['Insufficient stock for car part: ' . $carPart->name],
                ]);
            }

            $totalPrice += $carPart->price * $item['quantity'];
            $items[] = [
                'car_part_id' => $carPart->id,
                'quantity' => $item['quantity'],
                'total_price' => $carPart->price * $item['quantity'],
            ];
        }

        // Create the order
        $order = Order::create([
            'status' => 'pending',
            // 'total_price' => $totalPrice,
        ]);

        // Create order items
        foreach ($items as $item) {
            $order->items()->create($item);

            // Update stock quantity
            $carPart = CarPart::find($item['car_part_id']);
            $carPart->decrement('stock_quantity', $item['quantity']);
        }

        return new OrderResource($order);
    }

    // Update order status
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,completed,canceled',
        ]);

        $order->update(['status' => $request->status]);

        return new OrderResource($order);
    }

    // List all orders
    public function index()
    {
        $orders = Order::with('items')->paginate(10);
        return OrderResource::collection($orders);
    }

    // Show a specific order
    public function show(Order $order)
    {
        return new OrderResource($order);
    }
}