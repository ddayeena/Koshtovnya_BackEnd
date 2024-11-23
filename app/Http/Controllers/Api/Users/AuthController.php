<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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

        // Перевіряємо, чи користувач існує і чи правильний пароль
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function register(Request $request)
    {
        // Перевірка вхідних даних
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8', 
        ],[
            'first_name.required' => 'Ім’я є обов’язковим.',
            'first_name.string' => 'Ім’я має бути текстом.',
            'first_name.max' => 'Ім’я не може містити більше ніж 255 символів.',
        
            'last_name.required' => 'Прізвище є обов’язковим.',
            'last_name.string' => 'Прізвище має бути текстом.',
            'last_name.max' => 'Прізвище не може містити більше ніж 255 символів.',
        
            'email.required' => 'Електронна пошта є обов’язковою',
            'email.max' => 'Електронна пошта не може містити більше ніж 255 символів.',
            'email.unique' => 'Ця електронна пошта вже використовується.',
            'email.email' => 'Невірний формат електронної пошти.',
        
            'password.required' => 'Пароль є обов’язковим.',
            'password.string' => 'Пароль має бути текстом.',
            'password.min' => 'Пароль повинен містити щонайменше 8 символів.',
        ]);

        // Створення нового користувача
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($request->password), 
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'token' => $token,
            'user' => $user,
        ], 201); 
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['message' => 'Logged out']);
    }
}
