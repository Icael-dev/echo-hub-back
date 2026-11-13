<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    /**
     * Get the user's withdrawal pass (most recent order).
     *
     * Returns: { ref, hubName, hubAddress, slot, crateNumber, coldStorage, status, items }
     * The frontend expects the WithdrawalPass interface.
     */
    public function userWithdrawals(Request $request): JsonResponse
    {
        $user = $request->user();

        // Get the most recent order for the authenticated user
        $order = Order::with(['hub', 'items.product.producer'])
            ->where('user_id', $user->id)
            ->latest()
            ->first();

        if (!$order) {
            return response()->json(['message' => 'No withdrawal found'], 404);
        }

        // Build the items list
        $items = $order->items->map(function (OrderItem $item) {
            return [
                'name'     => $item->product->name,
                'subtitle' => $item->product->producer->name ?? '',
                'icon'     => 'inventory-2',
            ];
        })->toArray();

        // Generate crate number and cold storage based on order ID
        $crateNumber = 'BAC #' . str_pad((string) $order->id, 2, '0', STR_PAD_LEFT);
        $coldStorage = 'B' . (($order->id % 5) + 1);

        return response()->json([
            'ref'         => $order->order_id,
            'hubName'     => $order->hub->name ?? '',
            'hubAddress'  => ($order->hub->address ?? '') . ', ' . ($order->hub->city ?? ''),
            'slot'        => $order->slot ?? '',
            'crateNumber' => $crateNumber,
            'coldStorage' => $coldStorage,
            'status'      => $this->mapStatus($order->status),
            'items'       => $items,
        ]);
    }

    /**
     * Map internal order status to frontend-friendly status.
     */
    protected function mapStatus(string $status): string
    {
        return match ($status) {
            'pending'    => 'En préparation',
            'confirmed'  => 'Prêt & Réfrigéré',
            'ready'      => 'Prêt & Réfrigéré',
            'withdrawn'  => 'Retiré',
            'cancelled'  => 'Annulé',
            default      => 'En préparation',
        };
    }
}
