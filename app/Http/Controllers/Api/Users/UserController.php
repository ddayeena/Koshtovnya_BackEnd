<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\UserResource;
use App\Mail\WelcomeMail;
use App\Models\Cart;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        return response()->json([
            'message' => 'Profile successful',
            'user' => UserResource::make(auth()->user()),
        ], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request)
    {
        $user = $request->user();

        // Validate data
        $data = $request->validated();

        //Filter data without null
        $filteredData = array_filter($data, function ($value) {
            return !is_null($value);
        });

        //Update User
        $user->update($filteredData);

        return response()->json([
            'message' => 'User data updated successfully.',
            'user' => $user
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        //Check if the current password is correct
        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json(['message' => 'Invalid current password.'], 400);
        }

        // Update the password
        $request->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message' => 'Password was changed successfully.']);
    }

    public function verify(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        $user = User::where('email', $request->email)
            ->where('verification_code', $request->code)
            ->where('verification_expires_at', '>=', now())
            ->first();

        if (!$user) {
            return response()->json(['message' => 'Invalid or expired code.'], 400);
        }

        // Verify email
        $user->update([
            'verification_code' => null,
            'verification_expires_at' => null,
            'email_verified_at' => now(),
        ]);

        // Create cart and wishlist for user
        Wishlist::create(['user_id' => $user->id]);
        Cart::create(['user_id' => $user->id]);

        //Send welcome email to the new user
        Mail::to($user->email)->send(new WelcomeMail($user));

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful. Email is verified.',
            'token' => $token,
            'user' => $user,
        ], 201);
    }
}
