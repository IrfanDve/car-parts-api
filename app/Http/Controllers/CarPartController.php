<?php

namespace App\Http\Controllers;

use App\Models\CarPart;
use Illuminate\Http\Request;
use App\Http\Resources\CarPartResource;
use Illuminate\Validation\ValidationException;

class CarPartController extends Controller
{
    /**
     * List all car parts with filtering and pagination.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
           
            $query = CarPart::query();

            // Filter by category
            if ($request->filled('category')) {
                $query->where('category', $request->input('category'));
            }

            // Filter by price range
            if ($request->filled('min_price') && $request->filled('max_price')) {
                $query->whereBetween('price', [
                    (float) $request->input('min_price'),
                    (float) $request->input('max_price')
                ]);
            }

            
            $perPage = $request->get('per_page', 10);
            $carParts = $query->paginate($perPage);

           
            return response()->json([
                'status' => true,
                'message' => 'Car parts retrieved successfully.',
                'data' => CarPartResource::collection($carParts), // Use resource for formatting
                'meta' => [
                    'current_page' => $carParts->currentPage(),
                    'first_page_url' => $carParts->url(1),
                    'last_page_url' => $carParts->url($carParts->lastPage()),
                    'next_page_url' => $carParts->nextPageUrl(),
                    'prev_page_url' => $carParts->previousPageUrl(),
                    'per_page' => $carParts->perPage(),
                    'total' => $carParts->total(),
                ]
            ]);
        } catch (\Exception $e) {
          
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving car parts.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new car part.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
          
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'stock_quantity' => 'required|integer|min:0',
            ]);

            
            $carPart = CarPart::create($validated);

           
            return response()->json([
                'status' => true,
                'message' => 'Car part created successfully.',
                'data' => new CarPartResource($carPart),
            ], 201);
        } catch (ValidationException $e) {
            
            return response()->json([
                'status' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
          
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the car part.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show a specific car part.
     *
     * @param CarPart $carPart
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(CarPart $carPart)
    {
        try {
           
            return response()->json([
                'status' => true,
                'message' => 'Car part retrieved successfully.',
                'data' => new CarPartResource($carPart),
            ]);
        } catch (\Exception $e) {
           
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while retrieving the car part.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a car part.
     *
     * @param Request $request
     * @param CarPart $carPart
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CarPart $carPart)
    {
        try {
            
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'category' => 'sometimes|string|max:255',
                'price' => 'sometimes|numeric|min:0',
                'stock_quantity' => 'sometimes|integer|min:0',
            ]);

          
            $carPart->update($validated);

          
            return response()->json([
                'status' => true,
                'message' => 'Car part updated successfully.',
                'data' => new CarPartResource($carPart),
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
                'message' => 'An error occurred while updating the car part.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a car part.
     *
     * @param CarPart $carPart
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CarPart $carPart)
    {
        try {

            $carPart->delete();

            return response()->json([
                'status' => true,
                'message' => 'Car part deleted successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'An error occurred while deleting the car part.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}