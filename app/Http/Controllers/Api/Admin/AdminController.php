<?php

namespace App\Http\Controllers\Api\Admin;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use App\Mail\AdminInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class AdminController extends Controller
{
    public function addAdmin(Request $request)
    {
        $data = $request->validate( [
            'first_name' => 'required|string|max:50',
            'second_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|in:admin,manager', 
        ]);
        $password = Str::random(10);

        // Create admin
        $admin = User::create([
            'first_name' => $request->first_name,
            'second_name' => $request->second_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => $request->role,
        ]);

        //Send invitation to admin
        Mail::to($admin->email)->send(new AdminInvitation($admin, $password));

        return response()->json([
            'message' => 'Admin/manager added successfully',
            'admin' => $admin
        ], 201);
    }
}
