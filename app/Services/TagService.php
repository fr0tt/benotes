<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tag;
use App\Models\PostTag;

class TagService
{
    public function saveTag(string $name, int $user_id): Tag|null
    {
        if (Tag::where('name', $name)->where('user_id', $user_id)->exists()) {
            return null;
        }

        $tag = Tag::create([
            'name'    => $name,
            'user_id' => $user_id
        ]);

        return $tag;
    }
}
