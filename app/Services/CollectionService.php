<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;

use App\Models\Collection;

class CollectionService
{

    public function store($name, $user_id, $parent_collection_id, $icon_id)
    {
        $collection = Collection::create([
            'name' => $name,
            'user_id' => $user_id,
            'parent_id' => $parent_collection_id,
            'icon_id' => $icon_id
        ]);
        return $collection;
    }

    public function update($id, $name, $parent_collection_id, $is_root, $icon_id)
    {
        $attributes = collect([
            'name' => $name,
            'icon_id' => $icon_id
        ])->filter()->all();

        if ($is_root) {
            $attributes['parent_id'] = null;
        } else if (isset($parent_collection_id)) {
            $attributes['parent_id'] = $parent_collection_id;
        }

        $collection = Collection::find($id);
        $collection->update($attributes);
        return $collection;
    }

    public function delete($id, $is_nested, $user_id)
    {
        if ($is_nested) {
            Collection::where('user_id', $user_id)->where('parent_id', $id)->delete();
        }
        Collection::find($id)->delete();
    }
}
