<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products with producer info.
     *
     * Supports optional query parameters:
     * - category: filter by category (e.g., "Légumes & Fruits bio")
     * - producerId: filter by producer ID
     *
     * Returns an array matching the Product interface:
     * { id, producerId, producerName, name, subtitle, price, unit, distanceKm, image, category, inStock }
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::with('producer')->where('in_stock', true)->orderBy('id');

        // Filter by category if provided
        if ($request->has('category')) {
            $category = $request->input('category');
            $query->where('category', 'like', '%' . $category . '%');
        }

        // Filter by producerId if provided
        if ($request->has('producerId')) {
            $query->where('producer_id', $request->input('producerId'));
        }

        $products = $query->get();

        $result = $products->map(function ($product) {
            return [
                'id'           => $product->id,
                'producerId'   => $product->producer_id,
                'producerName' => $product->producer->name ?? '',
                'name'         => $product->name,
                'subtitle'     => $product->subtitle,
                'price'        => (float) $product->price,
                'unit'         => $product->unit,
                'distanceKm'   => (float) $product->distance_km,
                'image'        => $product->image,
                'category'     => $product->category,
                'inStock'      => $product->in_stock,
            ];
        });

        return response()->json($result);
    }
}
