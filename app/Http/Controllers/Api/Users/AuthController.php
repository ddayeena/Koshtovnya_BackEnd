<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\RegisterRequest;
use App\Mail\VerificationCodeMail;
use App\Models\User;
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
        // Validate request
        $request->validated();
    
        // Check if user already exists and hasn't verified yet
        $existingUser = User::where('email', $request->email)->first();
    
        if ($existingUser && $existingUser->verification_code !== null) {
            // Delete user that didnt finish registration
            $existingUser->delete();
        } elseif ($existingUser) {
            return response()->json(['message' => 'This email is already registered.'], 400);
        }
    
        // Generate 6-digit code
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
    
        // Send email with code
        Mail::to($user->email)->send(new VerificationCodeMail($verificationCode));
    
        return response()->json(['message' => 'Code sent to the email']);
    }
    

    public function sendCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);
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
        $user->update(['verification_code' => $code, 'verification_expires_at' => Carbon::now()->addMinutes(15)]);


        Mail::to($user->email)->send(new VerificationCodeMail($code));

        // Prevent spam (wait 1 min)
        Cache::put('code_sent_' . $user->id, true, now()->addMinute());

        return response()->json(['message' => 'Code sent to the email']);
    }

    public function verifyResetCode(Request $request)
    {
        // Validate data
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        // Get user
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check code
        if ($user->verification_code !== $request->code) {
            return response()->json(['message' => 'Invalid verification code'], 400);
        }
        if ($user->verification_expires_at < now()) {
            return response()->json(['message' => 'Verification code has expired'], 400);
        }

        //Give permission to reset password
        $user->update([
            'verification_code' => null,
            'verification_expires_at' => null,
            'password_reset_verified' => 1,
        ]);


        return response()->json(['message' => 'Code verified, you can now reset your password']);
    }

    public function resetPassword(Request $request)
    {
        //Validate data
        $request->validate([
            'email' => 'required|email',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
        //Get user
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['message' => 'Invalid request'], 400);
        }
        // Check if code verified
        if (!$user->password_reset_verified) {
            return response()->json(['message' => 'Password reset code not verified'], 403);
        }

        // Update password
        $user->update([
            'password' => Hash::make($request->new_password),
            'password_reset_verified' => null, 
        ]);

        return response()->json(['message' => 'Password was changed successfully.']);
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
