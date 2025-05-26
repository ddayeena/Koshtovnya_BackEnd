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
        $googleUser = Socialite::driver("google")->stateless()->user(); 

        $fullName = $googleUser->name;
        $nameParts = explode(' ', $fullName);

        // Get first name and last name from fullname
        $firstName = $nameParts[0] ?? null;
        $lastName = $nameParts[1] ?? null;

        $user = User::where('google_id', $googleUser->id)->first();

        if (!$user) {
            $user = User::create([
                'google_id' => $googleUser->id,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $googleUser->email,
                'password' => bcrypt(Str::random(8))
            ]);
        
            // Створюємо cart і wishlist тільки для нових користувачів
            Wishlist::create(['user_id' => $user->id]);
            Cart::create(['user_id' => $user->id]);
        
            Mail::to($user->email)->send(new WelcomeMail($user));
        }

        if (!$user->access) {
            return redirect()->away("https://koshtovnya-front-end-33rd.vercel.app/login?error=" . urlencode("Your account has been banned."));
        }
        
        Auth::login($user);        

        $token = $user->createToken('auth-token')->plainTextToken;

        $userData = [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'role' => $user->role
        ];
    
        return redirect()->away("https://koshtovnya-front-end-33rd.vercel.app/login?token={$token}&user=" . urlencode(json_encode($userData)));
    }
}   
