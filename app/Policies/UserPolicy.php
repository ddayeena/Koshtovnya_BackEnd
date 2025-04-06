<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Checking whether a user can view a list of other users
     */
    public function viewAny(User $authUser, $role = null)
    {
        if ($authUser->role === 'manager' && $role === 'employee') {
            return false;
        }

        return true;
    }

    /**
     * Checking whether a user can create new users
     */
    public function create(User $authUser, $newUserRole)
    {
        if ($authUser->role === 'admin' && $newUserRole !== 'user') {
            return false;
        }

        return true;
    }

    /**
     * Checking if a user can update others
     */
    public function update(User $authUser, User $user, ?string $newRole = null): bool
    {
        //A user can only update themselves and not change their role
        if ($authUser->id === $user->id) {
            return is_null($newRole);
        }

        // The manager can only update users and not change their role
        if ($authUser->role === 'manager' && $user->role === 'user') {
            return is_null($newRole);
        }

        // Admin can update admins, managers, users, but not super admins
        if ($authUser->role === 'admin') {
            if ($user->role === 'superadmin' || $newRole === 'superadmin') {
                return false;
            }
            return true; 
        }

        // Superadmin can change everyone and all roles
        if ($authUser->role === 'superadmin') {
            return true;
        }

        return false;
    }

    /**
     * Checking whether a user can delete other users
     */
    public function delete(User $authUser, User $user)
    {
        if ($authUser->id === $user->id) {
            return false;
        }

        if ($authUser->role === 'admin' && $user->role !== 'user') {
            return false;
        }

        return true;
    }
}
