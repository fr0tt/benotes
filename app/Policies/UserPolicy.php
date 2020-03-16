<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

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
        return $authUser->id === $user->user_id;
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
