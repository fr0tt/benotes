<?php

namespace App\Policies;

use App\User;
use App\Share;

use Illuminate\Auth\Access\HandlesAuthorization;

class SharePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can delete the share.
     *
     * @param  \App\User  $user
     * @param  \App\Share  $share
     * @return mixed
     */
    public function delete(User $user, Share $share)
    {
        return $user->id === $share->created_by;
    }

}
