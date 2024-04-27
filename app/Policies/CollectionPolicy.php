<?php

namespace App\Policies;

use App\Models\PrivateShare;
use App\Models\PublicShare;
use App\Models\Share;
use App\Models\User;
use App\Models\Collection;
use App\Services\CollectionService;
use Illuminate\Auth\Access\HandlesAuthorization;

class CollectionPolicy
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
     * Determine whether the user can view the collection.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Collection  $collection
     * @return mixed
     */
    public function view(User|PublicShare $user, Collection $collection)
    {
        if ($user instanceof PublicShare) {
            return $user->collection_id === $collection->id;
        }
        if ($user->id === $collection->user_id) {
            return true;
        }
        return CollectionService::isSharedWith($user, $collection);
    }

    /**
     * Determine whether the user can update the collection.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Collection  $collection
     * @return mixed
     */
    public function update(User $user, Collection $collection)
    {
        if ($user->id === $collection->user_id)
            return true;

        if (empty($collection->parent_id))
            return false;

        return CollectionService::isSharedWith($user, $collection);
    }

    /**
     * Determine whether the user can delete the collection.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Collection  $collection
     * @return mixed
     */
    public function delete(User $user, Collection $collection)
    {
        if ($user->id === $collection->user_id) {
            return true;
        }

        if (CollectionService::isSharedRootCollection($collection)) {
            return false;
        }
        return CollectionService::isSharedWith($user, $collection);
    }

    /**
     * Determine whether the user can inherit the collection.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Collection  $collection
     * @return mixed
     */
    public function inherit(User $user, Collection $parentCollection)
    {
        if ($user->id === $parentCollection->user_id) {
            return true;
        }

        return CollectionService::isSharedWith($user, $parentCollection);
    }

    public function move(User $user, Collection $collection, Collection|null $parentCollection)
    {
        if ($collection->user_id === $user->id)
            return true;

        if (!empty($parentCollection)
            && $parentCollection->root_collection_id === $collection->root_collection_id)
            return true;

        $ids = $collection->descendantsAndSelf()->pluck('id');
        return !PrivateShare
            ::whereIn('collection_id', $ids)
            ->where('created_by', '!=', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can create a post inside the collection.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Collection  $collection
     * @return mixed
     */
    public function fill(User $user, Collection $collection)
    {
        if ($user->id === $collection->user_id)
            return true;

        return CollectionService::isSharedWith($user, $collection);
    }

    public function share(User $user, Collection $collection)
    {
        return $user->id === $collection->user_id;
    }
}
