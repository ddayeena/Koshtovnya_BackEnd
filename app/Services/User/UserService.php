<?php

namespace App\Services\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Laravel\Sanctum\PersonalAccessToken;

class UserService
{
    public function getUserFromRequest(Request $request)
    {
        // Get token
        $token = $request->bearerToken();
        Log::info("Token: " . $token);

        if ($token) {
            // If the token exists, validate it
            $accessToken = PersonalAccessToken::findToken($token);
            if ($accessToken) {
                // If the token is valid, get the user
                return $accessToken->tokenable;
            }
        }

        // If there is no token or if the token is not valid, return null
        return null;
    }
}
