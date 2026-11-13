<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Hub;
use Illuminate\Http\JsonResponse;

class HubController extends Controller
{
    /**
     * Get the active hub.
     *
     * Returns: { id, name, address, city, collectionTime, closingCountdown }
     * The frontend expects camelCase keys matching the Hub interface.
     */
    public function active(): JsonResponse
    {
        $hub = Hub::where('is_active', true)->first();

        if (!$hub) {
            return response()->json(['message' => 'No active hub found'], 404);
        }

        return response()->json([
            'id'               => $hub->id,
            'name'             => $hub->name,
            'address'          => $hub->address,
            'city'             => $hub->city,
            'collectionTime'   => $hub->collection_time,
            'closingCountdown' => $hub->closing_countdown,
        ]);
    }

    /**
     * Get all hubs.
     *
     * Returns: [{ id, name, address, city, collectionTime, closingCountdown }, ...]
     * The frontend expects an array of Hub objects in camelCase.
     */
    public function index(): JsonResponse
    {
        $hubs = Hub::all()->map(function ($hub) {
            return [
                'id'               => $hub->id,
                'name'             => $hub->name,
                'address'          => $hub->address,
                'city'             => $hub->city,
                'collectionTime'   => $hub->collection_time,
                'closingCountdown' => $hub->closing_countdown,
            ];
        });

        return response()->json($hubs);
    }
}
