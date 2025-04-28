<?php

namespace App\Services\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    public function applySorting(Builder $query, ?string $role, ?string $sortBy, string $sortOrder = 'asc'): Builder
    {
        //Allow sort fields for each role
        $allowedSortFields = [
            'employee' => ['id', 'first_name', 'email', 'date', 'role', 'phone_number'],
            'user' => ['id', 'first_name', 'last_name', 'email', 'date', 'order_id'],
        ][$role] ?? ['id', 'first_name', 'last_name', 'email', 'date', 'order_id'];

        //Sort
        if ($sortBy && in_array($sortBy, $allowedSortFields) && in_array($sortOrder, ['asc', 'desc'])) {
            if ($sortBy === 'order_id' && $role !== 'employee') {
                $query->leftJoin('orders', 'users.id', '=', 'orders.user_id')
                      ->select('users.*')
                      ->addSelect(DB::raw('MAX(orders.id) as last_order_id'))
                      ->groupBy('users.id')
                      ->orderBy('last_order_id', $sortOrder);
            } else {
                $query->orderBy(
                    $sortBy === 'date' ? 'created_at' : $sortBy,
                    $sortOrder
                );
            }
        }

        return $query;
    }
}
