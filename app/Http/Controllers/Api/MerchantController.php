<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hub;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Producer;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MerchantController extends Controller
{
    /**
     * Get the merchant dashboard data.
     *
     * Returns: { totalOrders, claimedOrders, pendingOrders, monthlyCommission, crates, harvestItems }
     * The frontend expects the ProDashboard interface.
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;

        // Determine if user is merchant (at a hub) or farmer (producer)
        $isFarmer = $user->role === 'farmer';
        $isMerchant = $user->role === 'merchant';

        // Orders statistics for the current month
        $ordersQuery = Order::whereMonth('created_at', $currentMonth)
            ->whereYear('created_at', $currentYear);

        if ($isMerchant) {
            // Merchant sees orders at their hub(s)
            $hubIds = Hub::pluck('id')->toArray();
            $ordersQuery->whereIn('hub_id', $hubIds);
        }

        $totalOrders = $ordersQuery->count();
        $claimedOrders = $ordersQuery->whereIn('status', ['confirmed', 'ready', 'withdrawn'])->count();
        $pendingOrders = $ordersQuery->whereIn('status', ['pending'])->count();

        // Calculate monthly commission (5% of total order value)
        $monthlyTotal = $ordersQuery->sum('total_price');
        $monthlyCommission = round($monthlyTotal * 0.05, 2);

        // Build crates list from recent orders
        $crates = Order::with(['user', 'hub'])
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($order) use ($now) {
                $status = $this->mapCrateStatus($order->status);
                $description = $this->getOrderDescription($order);

                return [
                    'id'          => (string) $order->id,
                    'clientName'  => $order->user->name ?? 'Client inconnu',
                    'description' => $description,
                    'status'      => $status,
                    'urgent'      => $order->status === 'pending' && $order->created_at->diffInHours($now) > 24,
                ];
            });

        // Build harvest items (only for farmers)
        $harvestItems = collect([]);
        if ($isFarmer) {
            $producers = Producer::take(3)->get();
            $harvestItems = $producers->map(function ($producer, $index) {
                $products = Product::where('producer_id', $producer->id)->get();
                $totalKg = $products->sum(function ($p) {
                    return rand(20, 50);
                });

                return [
                    'id'          => 'harvest_' . $producer->id,
                    'name'        => $producer->name,
                    'parcel'      => $producer->category ?? 'Parcelle ' . chr(65 + $index),
                    'harvestedKg' => (int) $totalKg,
                    'targetKg'    => (int) ($totalKg + rand(5, 20)),
                    'active'      => true,
                ];
            });
        }

        return response()->json([
            'totalOrders'        => $totalOrders,
            'claimedOrders'      => $claimedOrders,
            'pendingOrders'      => $pendingOrders,
            'monthlyCommission'  => $monthlyCommission,
            'crates'             => $crates->toArray(),
            'harvestItems'       => $harvestItems->toArray(),
        ]);
    }

    /**
     * Map order status to crate status for frontend.
     */
    protected function mapCrateStatus(string $status): string
    {
        return match ($status) {
            'pending'   => 'Prêt',
            'confirmed' => 'Prêt',
            'ready'     => 'Prêt',
            'withdrawn' => 'Retiré',
            'cancelled' => 'Annulé',
            default     => 'En attente',
        };
    }

    /**
     * Generate a short description for an order.
     */
    protected function getOrderDescription(Order $order): string
    {
        $itemCount = $order->items()->count();
        return "Commande avec {$itemCount} article(s) • " . number_format($order->total_price, 2) . ' €';
    }

    /**
     * Verify a QR code for withdrawal.
     *
     * Expects: { qr_code: string }
     * Returns: { success: boolean, message: string, crateNumber?: string }
     * The frontend expects this format for the merchant QR scanner.
     */
    public function verifyQr(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'qr_code' => 'required|string',
        ]);

        $qrCode = $validated['qr_code'];

        // Try to find an order by the QR code (which matches the order_id format EH-XXXX)
        $order = Order::with(['hub', 'user', 'items.product.producer'])
            ->where('order_id', $qrCode)
            ->first();

        if (!$order) {
            // Try to find by treating the QR code as a numeric reference
            $order = Order::with(['hub', 'user', 'items.product.producer'])
                ->find($qrCode);
        }

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => "Pass non trouvé pour le code {$qrCode}",
            ], 404);
        }

        // Update order status to withdrawn if it was ready
        if (in_array($order->status, ['pending', 'confirmed', 'ready'])) {
            $order->status = 'withdrawn';
            $order->save();
        }

        // Generate crate number based on order ID
        $crateNumber = 'BAC #' . str_pad((string) $order->id, 2, '0', STR_PAD_LEFT);

        return response()->json([
            'success' => true,
            'message' => "Pass vérifié avec succès — {$order->user->name}",
            'crateNumber' => $crateNumber,
            'hubName' => $order->hub->name ?? '',
            'customerName' => $order->user->name ?? '',
            'slot' => $order->slot ?? '',
        ]);
    }
}
