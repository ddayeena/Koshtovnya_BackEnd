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
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    public function store(Request $request)
    {
        $data = $request->validate( [
            'first_name' => 'required|string|max:50',
            'second_name' => 'required|string|max:50',
            'last_name' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users',
            'phone_number' => 'required|string|max:20|regex:/^\+?[0-9\s\-]+$/', 
            'role' => 'required|in:admin,manager,superadmin', 
        ]);
        $password = Str::random(10);

        // Create admin
        $admin = User::create([
            'first_name' => $data['first_name'],
            'second_name' => $data['second_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'phone_number' => $data['phone_number'],
            'role' => $data['role'],
        ]);

        //Send invitation to admin
        Mail::to($admin->email)->send(new AdminInvitation($admin, $password));

        return response()->json([
            'message' => 'Admin/manager added successfully',
            'admin' => $admin
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
