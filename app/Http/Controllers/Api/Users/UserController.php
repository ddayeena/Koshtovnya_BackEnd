<?php

namespace App\Http\Controllers\Api\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Resources\UserProfileResource;
use App\Http\Resources\UserResource;
use App\Mail\AdminInvitation;
use App\Mail\BanMail;
use App\Mail\UserInvitation;
use App\Mail\WelcomeMail;
use App\Models\Cart;
use App\Models\Order;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\User\UserService;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, UserService $userService)
    {
        $query = User::query();
        $role = $request->get('role');

        //Get role
        if ($role === 'employee') {
            $query->whereIn('role', ['admin', 'superadmin', 'manager']);
        } elseif (in_array($role, ['admin', 'manager', 'superadmin', 'user'])) {
            $query->where('role', $role);
        }

        $this->authorize('viewAny', [User::class, $role]);

        //Sort
        $query = $userService->applySorting($query, $role, $request->get('sort_by'), $request->get('sort_order', 'asc'));

        return UserResource::collection($query->paginate(10));
    }

    //search users 
    public function search(Request $request, string $name)
    {
        if ($request->role === 'employee' && $request->user()->role === 'manager') {
            return response()->json(['message' => 'Access denied'], 403);
        }
        // Search users
        $query = User::where(function ($query) use ($name) {
            $query->where('id', $name)
                ->orWhere('first_name', 'LIKE', "%{$name}%")
                ->orWhere('second_name', 'LIKE', "%{$name}%")
                ->orWhere('last_name', 'LIKE', "%{$name}%")
                ->orWhere('phone_number', 'LIKE', "%{$name}%")
                ->orWhere('email', 'LIKE', "%{$name}%");
        });


        //Filter by type
        if ($request->has('role')) {
            if ($request->role === 'user') {
                $query->where('role', 'user');
            } elseif ($request->role === 'employee') {
                $query->whereIn('role', ['admin', 'superadmin', 'manager']);
            }
        }

        //Get users
        $users = $query->get();
        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $this->authorize('create', [User::class, $data['role']]);

        $password = Str::random(10);

        // Create user
        $user = User::create([
            'first_name' => $data['first_name'],
            'second_name' => $data['second_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'phone_number' => $data['phone_number'],
            'role' => $data['role'],
        ]);

        //Send invitation to user
        Mail::to($user->email)->send(new UserInvitation($user, $password));

        return response()->json([
            'message' => 'User added successfully',
            'admin' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        return response()->json([
            'message' => 'Profile successful',
            'user' => UserProfileResource::make(auth()->user()),
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRequest $request, $id)
    {
        $authUser = $request->user();
        $user = User::findOrFail($id);
        $newRole = $request->input('role');

        if (!$authUser->can('update', [$user, $newRole])) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        if ($request->has('role')) {
            $user->role = $newRole;
        }

        return $this->performUpdate($user, $request);
    }

    private function performUpdate(User $user, UpdateRequest $request)
    {
        $data = array_filter($request->validated(), function ($value) {
            return !is_null($value);
        });

        $user->update($data);

        return response()->json([
            'message' => 'User data updated successfully.',
            'user' => UserProfileResource::make($user),
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $userToDelete = User::findOrFail($id);
        $this->authorize('delete', $userToDelete);

        Order::where('user_id', $userToDelete->id)->update(['user_id' => null]);

        $userToDelete->delete();
        return response()->json(['message' => 'User deleted successfully']);
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

    public function ban(string $id)
    {
        $user = User::findOrFail($id);

        $user->access = 0;
        $user->is_permanently_banned = 1; 
        $user->save();

        $user->tokens()->delete();

        Mail::to($user->email)->send(new BanMail($user));

        if($user->role !== 'user'){
            $user->role = 'user';
        }

        return response()->json(['message' => 'User is banned.']);
    }

    public function unban(string $id)
    {
        $user = User::findOrFail($id);

        $user->access = 1;
        $user->banned_until = null;
        $user->is_permanently_banned = false;
        $user->was_banned_before = true;

        $user->save();

        return response()->json(['message' => 'User is unbanned.']);
    }
}
