<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producer;
use Illuminate\Http\JsonResponse;

class ProducerController extends Controller
{
    /**
     * Get producer details by ID.
     *
     * Returns: { id, name, category, distanceKm, bioCertified, avatar, coverImage,
     *            description, fairSharePercent, farmersName, quote, varietiesCount, hectares }
     * The frontend expects camelCase keys matching the Producer interface.
     */
    public function show(int $id): JsonResponse
    {
        $producer = Producer::with('products')->findOrFail($id);

        return response()->json([
            'id'              => $producer->id,
            'name'            => $producer->name,
            'category'        => $producer->category,
            'distanceKm'      => (float) $producer->distance_km,
            'bioCertified'    => $producer->bio_certified,
            'avatar'          => $producer->avatar,
            'coverImage'      => $producer->cover_image,
            'description'     => $producer->description,
            'fairSharePercent' => (float) $producer->fair_share_percent,
            'farmersName'     => $producer->farmers_name,
            'quote'           => $producer->quote,
            'varietiesCount'  => $producer->varieties_count,
            'hectares'        => (float) $producer->hectares,
        ]);
    }
}
