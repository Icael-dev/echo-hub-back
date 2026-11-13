<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Durée du token d'accès en minutes (15 minutes).
     */
    protected int $accessTokenTtl = 15;

    /**
     * Durée du refresh token en jours (7 jours).
     */
    protected int $refreshTokenTtlDays = 7;

    /**
     * Login and return a Sanctum access token + refresh token.
     *
     * Expects: { email, password }
     * Returns: { token, refresh_token, token_type, expires_in, user: { id, name, email, role } }
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke all existing refresh tokens for this user (single session per user)
        foreach ($user->refreshTokens()->valid()->get() as $token) {
            $token->revoke();
        }

        // Create a short-lived Sanctum access token (15 minutes)
        $accessToken = $user->createToken(
            'eco-hub-mobile',
            ['*'],
            now()->addMinutes($this->accessTokenTtl)
        )->plainTextToken;

        // Generate a refresh token
        $plainRefreshToken = Str::random(64);
        $hashedRefreshToken = hash('sha256', $plainRefreshToken);

        RefreshToken::create([
            'tokenable_id' => $user->id,
            'tokenable_type' => User::class,
            'token' => $hashedRefreshToken,
            'expires_at' => now()->addDays($this->refreshTokenTtlDays),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json([
            'token' => $accessToken,
            'refresh_token' => $plainRefreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTokenTtl * 60, // seconds
            'user'  => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ]);
    }

    /**
     * Refresh an access token using a valid refresh token.
     *
     * Implements token rotation: the old refresh token is revoked and a new one is issued.
     * Uses a database transaction to prevent race conditions (replay attacks).
     *
     * Expects: { refresh_token: string }
     * Returns: { token, refresh_token, token_type, expires_in }
     */
    public function refresh(Request $request): JsonResponse
    {
        $request->validate([
            'refresh_token' => 'required|string',
        ]);

        $hashedToken = hash('sha256', $request->refresh_token);

        $response = null;

        DB::transaction(function () use ($request, $hashedToken, &$response) {
            // Lock the refresh token row to prevent concurrent access
            $refreshTokenRecord = RefreshToken::where('token', $hashedToken)
                ->lockForUpdate()
                ->first();

            // If not found, the token was already used or is invalid
            if (!$refreshTokenRecord) {
                throw ValidationException::withMessages([
                    'refresh_token' => ['The refresh token has expired or is invalid.'],
                ]);
            }

            if (!$refreshTokenRecord->isValid()) {
                throw ValidationException::withMessages([
                    'refresh_token' => ['The refresh token has expired or is invalid.'],
                ]);
            }

            $user = $refreshTokenRecord->tokenable;

            if (!$user) {
                throw ValidationException::withMessages([
                    'refresh_token' => ['The refresh token has expired or is invalid.'],
                ]);
            }

            // Token rotation: revoke the old refresh token FIRST (within transaction)
            $refreshTokenRecord->update(['revoked_at' => now()]);

            // Revoke all other refresh tokens for this user (single session policy)
            $user->refreshTokens()
                ->where('id', '!=', $refreshTokenRecord->id)
                ->whereNull('revoked_at')
                ->where('expires_at', '>', now())
                ->update(['revoked_at' => now()]);

            // Create a new short-lived Sanctum access token
            $accessToken = $user->createToken(
                'eco-hub-mobile',
                ['*'],
                now()->addMinutes($this->accessTokenTtl)
            )->plainTextToken;

            // Generate a new refresh token (rotation)
            $plainRefreshToken = Str::random(64);
            $hashedRefreshToken = hash('sha256', $plainRefreshToken);

            RefreshToken::create([
                'tokenable_id' => $user->id,
                'tokenable_type' => User::class,
                'token' => $hashedRefreshToken,
                'expires_at' => now()->addDays($this->refreshTokenTtlDays),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $response = [
                'token' => $accessToken,
                'refresh_token' => $plainRefreshToken,
                'token_type' => 'Bearer',
                'expires_in' => $this->accessTokenTtl * 60,
            ];
        });

        return response()->json($response);
    }

    /**
     * Logout (revoke the current access token and all refresh tokens).
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the current Sanctum access token
        $request->user()->currentAccessToken()->delete();

        // Revoke all refresh tokens for this user
        foreach ($request->user()->refreshTokens()->get() as $token) {
            $token->revoke();
        }

        return response()->json(['message' => 'Logged out successfully']);
    }

    /**
     * Get the authenticated user.
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
            'role'  => $user->role,
        ]);
    }
}
