<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Submit a new order.
     *
     * Expects: { items: [{ product: Product, quantity: number }], hubId: number, slot: string }
     * Returns: { success: boolean, orderId: string }
     *
     * The frontend sends the full product object in each item, not just an ID.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product' => 'required|array',
            'items.*.product.id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'hubId' => 'required|integer|exists:hubs,id',
            'slot'  => 'nullable|string',
        ]);

        $userId = $request->user()?->id;

        // If not authenticated, find the first consumer user (mock mode fallback)
        if (!$userId) {
            $consumer = User::where('role', 'consumer')->first();
            $userId = $consumer?->id ?? null;
        }

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'No consumer user found. Please login first.',
            ], 401);
        }

        DB::beginTransaction();

        try {
            // Generate a unique order ID matching the frontend format: EH-XXXX
            $orderId = 'EH-' . str_pad((string) Order::count() + 1, 4, '0', STR_PAD_LEFT);

            // Calculate total price
            $totalPrice = 0;
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product']['id']);
                if ($product) {
                    $totalPrice += (float) $product->price * $item['quantity'];
                }
            }

            // Create the order
            $order = Order::create([
                'user_id'     => $userId,
                'hub_id'      => $validated['hubId'],
                'slot'        => $validated['slot'] ?? null,
                'total_price' => round($totalPrice, 2),
                'status'      => 'pending',
                'order_id'    => $orderId,
            ]);

            // Create order items
            foreach ($validated['items'] as $item) {
                $product = Product::find($item['product']['id']);
                if ($product) {
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $product->id,
                        'quantity'   => $item['quantity'],
                        'price'      => (float) $product->price,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'orderId' => $orderId,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
            ], 500);
        }
    }

    /**
     * Get all orders for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $userId = $request->user()?->id;

        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $orders = Order::where('user_id', $userId)
            ->with(['hub', 'items.product.producer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    /**
     * Get invoice URL for an order.
     *
     * The frontend expects: { url: string }
     * Returns a downloadable invoice URL for the given order.
     */
    public function invoice(string $orderId): JsonResponse
    {
        $order = Order::where('order_id', $orderId)->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Return invoice data — in production, this would generate a real PDF
        return response()->json([
            'url' => url('/api/orders/' . $orderId . '/invoice'),
            'orderId' => $order->order_id,
            'totalPrice' => (float) $order->total_price,
            'date' => $order->created_at->format('Y-m-d H:i'),
            'hubName' => $order->hub->name ?? '',
            'status' => $order->status,
        ]);
    }

    /**
     * Reorder the items from a previous order.
     *
     * Returns an array of Product objects matching the original order items.
     * The frontend expects: Product[]
     */
    public function reorder(string $orderId): JsonResponse
    {
        $order = Order::with('items.product.producer')
            ->where('order_id', $orderId)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Return the products from the original order in the format the frontend expects
        $products = $order->items->map(function (OrderItem $item) {
            return [
                'id'           => $item->product->id,
                'producerId'   => $item->product->producer_id,
                'producerName' => $item->product->producer->name ?? '',
                'name'         => $item->product->name,
                'subtitle'     => $item->product->subtitle ?? '',
                'price'        => (float) $item->product->price,
                'unit'         => $item->product->unit ?? '',
                'distanceKm'   => (float) ($item->product->producer->distance_km ?? 0),
                'image'        => $item->product->image ?? '',
                'category'     => $item->product->category ?? '',
                'inStock'      => $item->product->in_stock,
            ];
        });

        return response()->json($products);
    }
}
