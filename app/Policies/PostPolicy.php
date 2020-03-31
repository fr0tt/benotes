<?php

namespace App\Policies;

use App\User;
use App\Share;
use App\Post;
use Illuminate\Auth\Access\HandlesAuthorization;

class PostPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the post.
     *
     * @param  mixed      $user
     * @param  \App\Post  $post
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
     * @param  \App\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function update(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }

    /**
     * Determine whether the user can delete the post.
     *
     * @param  \App\User  $user
     * @param  \App\Post  $post
     * @return mixed
     */
    public function delete(User $user, Post $post)
    {
        return $user->id === $post->user_id;
    }

}
