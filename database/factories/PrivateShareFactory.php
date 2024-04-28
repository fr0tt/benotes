<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\Collection;

class PrivateShareFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'collection_id' => (Collection::count() > 0) ? Collection::first()->id : null,
            'user_id'       => User::latest()->first()->id,
            'created_by'    => User::first()->id,
        ];
    }
}
