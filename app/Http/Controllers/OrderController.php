<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CarPart;
use Illuminate\Http\Request;
use App\Http\Resources\OrderResource;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OrderController extends Controller
{
    /**
     * Place a new order.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
           
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.car_part_id' => 'required|exists:car_parts,id',
                'items.*.quantity' => 'required|integer|min:1',
            ]);

            // Calculate total price and check stock
            $totalPrice = 0;
            $items = [];

            foreach ($validated['items'] as $item) {
                $carPart = CarPart::find($item['car_part_id']);

                // Check stock availability
                if ($carPart->stock_quantity < $item['quantity']) {
                    throw ValidationException::withMessages([
                        'items' => ['Insufficient stock for car part: ' . $carPart->name],
                    ]);
                }

                // Calculate total price for the item
                $itemTotalPrice = $carPart->price * $item['quantity'];
                $totalPrice += $itemTotalPrice;

                // Prepare order items
                $items[] = [
                    'car_part_id' => $carPart->id,
                    'quantity' => $item['quantity'],
                    'total_price' => $itemTotalPrice,
                ];
            }

            // Create the order
            $order = Order::create([
                'status' => 'pending',
            ]);

            // Create order items and update stock
            foreach ($items as $item) {
                $order->items()->create($item);

                // Decrement stock quantity
                $carPart = CarPart::find($item['car_part_id']);
                $carPart->decrement('stock_quantity', $item['quantity']);
            }

            return response()->json([
                'status' => true,
                'message' => 'Order placed successfully.',
                'data' => new OrderResource($order),
            ], 201);
        } catch (ValidationException $e) {
            
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'An error occurred while placing the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update order status.
     *
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStatus(Request $request, Order $order)
    {
        try {
            
            $validated = $request->validate([
                'status' => 'required|in:pending,completed,canceled',
            ]);

            // Update the order status
            $order->update(['status' => $validated['status']]);

           
            return response()->json([
                'status' => true,
                'message' => 'Order status updated successfully.',
                'data' => new OrderResource($order),
            ]);
        } catch (ValidationException $e) {
           
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
           
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while updating the order status.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all orders.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            
            $orders = Order::with('items')->paginate(10);

           
            return response()->json([
                'status' => true,
                'message' => 'Orders retrieved successfully.',
                'data' => OrderResource::collection($orders),
                'meta' => [
                    'current_page' => $orders->currentPage(),
                    'first_page_url' => $orders->url(1),
                    'last_page_url' => $orders->url($orders->lastPage()),
                    'next_page_url' => $orders->nextPageUrl(),
                    'prev_page_url' => $orders->previousPageUrl(),
                    'per_page' => $orders->perPage(),
                    'total' => $orders->total(),
                ],
            ]);
        } catch (\Exception $e) {
           
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving orders.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific order.
     *
     * @param Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Order $order)
    {
        try {
           
            return response()->json([
                'status' => true,
                'message' => 'Order retrieved successfully.',
                'data' => new OrderResource($order),
            ]);
        } catch (\Exception $e) {
            
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving the order.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}