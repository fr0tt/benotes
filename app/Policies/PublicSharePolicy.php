<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Share;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicSharePolicy
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
     * Determine whether the user can delete the share.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Share  $share
     * @return mixed
     */
    public function delete(User $user, Share $share)
    {
        return $user->id === $share->created_by;
    }
}
