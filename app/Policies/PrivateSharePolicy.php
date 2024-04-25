<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\PrivateShare;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PrivateSharePolicy
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

    public function update(User $user, PrivateShare $share)
    {
        return $user->id === $share->created_by;
    }

    public function delete(User $user, PrivateShare $share)
    {
        return $user->id === $share->user_id || $user->id === $share->created_by;
    }
}
