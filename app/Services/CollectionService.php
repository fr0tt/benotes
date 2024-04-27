<?php

namespace App\Services;

use App\Models\PrivateShare;
use App\Models\User;
use App\Models\Collection;
use App\Models\Post;

class CollectionService
{

    private $parentChildMap = [];

    public function store($name, $owner_id, $parent_collection_id, $icon_id)
    {
        $is_being_shared = empty($parent_collection_id)
            ? false
            : Collection::find($parent_collection_id)->is_being_shared;
        return Collection::create([
            'name'            => $name,
            'user_id'         => $owner_id,
            'parent_id'       => $parent_collection_id,
            'icon_id'         => $icon_id,
            'is_being_shared' => $is_being_shared
        ]);
    }

    public function update(int $id, string|null $name, Collection|null $parentCollection, int|null $icon_id, int $user_id)
    {
        $attributes = collect([
            'name'    => $name,
            'icon_id' => $icon_id
        ])->filter()->all();

        $parent_id = null;
        if (!empty($parentCollection)) {
            $parent_id = $parentCollection->id;
            $user_id = $parentCollection->user_id;
        }

        $collection = Collection::find($id);

        $isBeingShared = $this->isBeingShared(
            $collection->id,
            $parent_id
        );
        $attributes['is_being_shared'] = $isBeingShared;
        if ($collection->is_being_shared !== $isBeingShared) {
            $collection
                ->descendants()
                ->update(['is_being_shared' => $isBeingShared]);
        }

        if ($collection->user_id !== $user_id) {
            $ids = $collection->descendantsAndSelf()->pluck('id');
            Post::whereIn('collection_id', $ids)
                ->where('user_id', '!=', $user_id)
                ->update(['user_id' => $user_id]);
        }

        $collection->update($attributes);

        if ($collection->parent_id !== $parent_id) {
            $collection->moveTo($parent_id, -1, $user_id);
        }

        return $collection;
    }

    public function delete(Collection $collection)
    {
        $collection->delete();
    }

    public static function isBeingShared(int $id, int|null $newParentId): bool
    {
        if (PrivateShare::where('collection_id', $id)->exists()) {
            return true;
        }
        if (empty($newParentId)) {
            return false;
        }
        return Collection::find($newParentId)->is_being_shared;
    }

    public static function isSharedWith(User $user, Collection $collection): bool
    {
        $root_share_collections = PrivateShare
            ::select('collection_id')
            ->where('user_id', $user->id)
            ->where('created_by', $collection->user_id);

        if ($root_share_collections->count() === 0) {
            return false;
        }

        $collections = Collection::whereIn('id', $root_share_collections)->get();
        foreach ($collections as $col) {
            if ($collection->isSelfOrDescendantOf($col)) {
                return true;
            }
        }
        return false;
    }

    public static function isSharedRootCollection(Collection $collection): bool
    {
        return PrivateShare
            ::where('collection_id', $collection->id)
            //->where('user_id', $user->id)
            //->where('created_by', $collection->user_id)
            ->exists();
    }

    public static function getSharedRootCollectionIds(int $user_id): \Illuminate\Support\Collection
    {
        return PrivateShare
            ::where('user_id', $user_id)
            ->pluck('root_collection_id');
    }

    public function getSharedCollections(int $user_id)
    {
        $shares = PrivateShare::select('collection_id')
            ->where('user_id', $user_id);
        $roots = Collection::whereIn('id', $shares)
            ->orderBy('name')
            ->get();
        return $roots->flatMap(function ($root) {
            return $root->descendantsAndSelf()->get();
        });
    }


}
