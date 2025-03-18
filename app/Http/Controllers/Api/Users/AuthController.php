<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterRequest;
use App\Mail\VerificationCodeMail;
use App\Mail\WelcomeMail;
use App\Models\Cart;
use App\Models\User;
use App\Models\Wishlist;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        // Check if the user exists and if the password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        // Check if the email is verified
        if (!$user->email_verified_at) {
            return response()->json(['message' => 'Please verify your email address first.'], 403);
        }

        // Create a token if the email is verified
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }


    public function register(RegisterRequest $request)
    {
        //Check data
        $request->validated();
        //Genereta code
        $verificationCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        // Create new user
        $user = User::create([
            'first_name' => $request->first_name,
            'second_name' => $request->second_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verification_code' => $verificationCode,
            'verification_expires_at' => Carbon::now()->addMinutes(15),
        ]);
        //Send email
        Mail::to($user->email)->send(new VerificationCodeMail($verificationCode));
        return response()->json(['message' => 'Code sent to the email']);
    }

    public function resendCode(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Checking if 1 minute has passed since the last send
        if (Cache::has('code_sent_' . $user->id)) {
            return response()->json(['message' => 'Please wait atleast 1 min before resending.'], 429);
        }

        // Generate new code
        $code =  str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user->update(['verification_code' => $code]);

        Mail::to($user->email)->send(new VerificationCodeMail($code));

        // Prevent spam (wait 1 min)
        Cache::put('code_sent_' . $user->id, true, now()->addMinute());

        return response()->json(['message' => 'Code sent to the email']);
    }


    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
