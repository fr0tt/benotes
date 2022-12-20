<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can create users.
     *
     * @param  \App\Models\User  $authUser
     * @return mixed
     */
    public function create(User $authUser)
    {
        return $authUser->permission === User::ADMIN;
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  \App\Models\User  $authUser
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function update(User $authUser, User $user)
    {
        return $authUser->id === $user->id
            ? Response::allow()
            : Response::deny('Only the user itself can change these information.');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\Models\User  $authUser
     * @return mixed
     */
    public function delete(User $authUser)
    {
        return $authUser->permission === User::ADMIN;
    }
}
