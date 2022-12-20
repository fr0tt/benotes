<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Post;
use App\Models\Share;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
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
     * Determine whether the user can view the post.
     *
     * @param  mixed      $user
     * @param  \App\Models\Post  $post
     * @return mixed
     */
    public function view($user, Post $post)
    {
        if ($user instanceof User)
            return $user->id === $post->user_id;
        else if ($user instanceof Share)
            return $user->collection_id === $post->collection_id;
    }

    /**
     * Determine whether the user can update the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return mixed
     */
    public function update(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the post.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Post  $post
     * @return mixed
     */
    public function delete(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }
}
