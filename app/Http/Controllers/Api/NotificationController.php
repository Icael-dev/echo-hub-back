<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PushToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Register or update an Expo push token for the authenticated user.
     *
     * Expects: { push_token: string }
     * Returns: { success: true }
     */
    public function registerPushToken(Request $request): JsonResponse
    {
        $request->validate([
            'push_token' => 'required|string',
        ]);

        // If the token already exists, just mark it active
        $existing = PushToken::where('push_token', $request->push_token)->first();
        if ($existing) {
            $existing->update([
                'user_id' => $request->user()->id,
                'active'  => true,
            ]);
            return response()->json(['success' => true]);
        }

        // Deactivate old tokens for this user
        PushToken::where('user_id', $request->user()->id)
            ->update(['active' => false]);

        // Create new push token record
        PushToken::create([
            'user_id'    => $request->user()->id,
            'push_token' => $request->push_token,
            'platform'   => $request->input('platform'),
            'active'     => true,
        ]);

        return response()->json(['success' => true]);
    }
}
