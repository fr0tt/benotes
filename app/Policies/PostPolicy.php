<?php

namespace App\Policies;

use App\Models\Collection;
use App\Models\PrivateShare;
use App\Models\PublicShare;
use App\Models\User;
use App\Models\Post;
use App\Services\CollectionService;
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
    public function view(User|PublicShare $user, Post $post)
    {
        if ($user instanceof User && $user->id === $post->user_id) {
            return true;
        }
        if ($user instanceof PublicShare) {
            return $user->collection_id === $post->collection_id;
        }
        // potential shared collection
        if (empty($post->collection_id))
            return false;
        return CollectionService::isSharedWith(
            $user,
            Collection::find($post->collection_id)
        );
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
        if ($user->id === $post->user_id)
            return true;

        // uncategorized posts should only belong to $user
        if (empty($post->collection_id))
            return false;

        return CollectionService::isSharedWith(
            $user,
            Collection::find($post->collection_id)
        );
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
        if ($user->id === $post->user_id)
            return true;

        if (empty($post->collection_id))
            return false;

        return CollectionService::isSharedWith(
            $user,
            Collection::find($post->collection_id)
        );
    }
}
