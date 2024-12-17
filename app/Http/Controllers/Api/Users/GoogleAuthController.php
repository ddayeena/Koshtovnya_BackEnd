<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Mail\WelcomeMail;
use App\Models\Cart;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    public function redirect()
    {
        return Socialite::driver("google")->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver("google")->user();

        $fullName = $googleUser->name;
        $nameParts = explode(' ', $fullName);

        // Get first name and last name from fullname
        $firstName = $nameParts[0] ?? null;
        $lastName = $nameParts[1] ?? null;

        //Create or update user
        $user = User::updateOrCreate(
            ['google_id' => $googleUser->id],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,

                'email' => $googleUser->email,
                'password' => bcrypt(Str::random(8))
            ]
        );

        Auth::login($user);

        // Create cart and wishlist for user
        Wishlist::create(['user_id' => $user->id]);
        Cart::create(['user_id' => $user->id]);

        //Send welcome email to the new user
        Mail::to($user->email)->send(new WelcomeMail($user));

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user
        ], 200);
    }
}
