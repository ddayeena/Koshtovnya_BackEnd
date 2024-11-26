<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
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
            'current_password'=>'required|string',
            'new_password'=>'required|string|min:8|confirmed',
        ]);

        //Check if the current password is correct
        if (!Hash::check($request->current_password, $request->user()->password)) {
            return response()->json(['message' => 'Invalid current password.'], 400);
        }

        // Update the password
        $request->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json(['message'=>'Password was changed successfully.']);
    }
}
