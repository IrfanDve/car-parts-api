<?php

namespace App\Http\Controllers;

use App\Models\CarPart;
use App\Http\Resources\CarPartResource;
use Illuminate\Http\Request;

class CarPartController extends Controller
{
    // List all car parts with filtering and pagination
    public function index(Request $request)
    {
        $query = CarPart::query();

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }        

        // Filter by price range
        if ($request->filled('min_price') && $request->filled('max_price')) {
            $query->whereBetween('price', [(float) $request->input('min_price'), (float) $request->input('max_price')]);
        }
        

        // Pagination with flexibility for items per page (default to 10)
        $perPage = $request->get('per_page', 10); 
        $carParts = $query->paginate($perPage);

        return CarPartResource::collection($carParts);
    }

    // Create a new car part
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
        ]);

        $carPart = CarPart::create($validated);

        return new CarPartResource($carPart);
    }

    // Show a specific car part
    public function show(CarPart $carPart)
    {
        return new CarPartResource($carPart);
    }

    // Update a car part
    public function update(Request $request, CarPart $carPart)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
        ]);

        $carPart->update($validated);

        return new CarPartResource($carPart);
    }

    // Delete a car part
    public function destroy(CarPart $carPart)
    {
        $carPart->delete();

        return response()->json(['message' => 'Car part deleted successfully']);
    }
}