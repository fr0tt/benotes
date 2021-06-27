<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create users.
     *
     * @param  \App\User  $authUser
     * @return mixed
     */
    public function create(User $authUser)
    {
        return $authUser->permission === User::ADMIN;
    }

    /**
     * Determine whether the user can update the user.
     *
     * @param  \App\User  $authUser
     * @param  \App\User  $user
     * @return mixed
     */
    public function update(User $authUser, User $user)
    {
        return $authUser->id === $user->id
            ? Response::allow()
            : Response::deny('Only the user himself can change these information.');
    }

    /**
     * Determine whether the user can delete the user.
     *
     * @param  \App\User  $authUser
     * @return mixed
     */
    public function delete(User $authUser)
    {
        return $authUser->permission === User::ADMIN;
    }

}
