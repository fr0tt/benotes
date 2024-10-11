<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Post;
use App\Models\Collection;

class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $collection_id = Collection::count() > 0 ? Collection::first()->id : null;
        return [
            'id' => $this->faker->randomNumber(),
            'title' => $this->faker->title(),
            'content' => $this->faker->sentence(),
            'collection_id' => $collection_id,
            'type' => Post::POST_TYPE_TEXT,
            'user_id' => User::first()->id,
            'order' => Post::where('collection_id', $collection_id)->count()
        ];
    }
}
