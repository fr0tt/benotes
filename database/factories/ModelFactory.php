<?php

use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;

use App\User;
use App\Post;
use App\Collection;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->email,
        'password' => Hash::make('test'),
        'permission' => 7
    ];
});

$factory->define(Collection::class, function (Faker $faker) {
    return [
        'name' => $faker->company,
        'user_id' => User::first()->id
    ];
});

$factory->define(Post::class, function (Faker $faker) {
    return [
        'id' => $faker->randomNumber,
        'title' => $faker->title,
        'content' => $faker->sentence,
        'collection_id' => null,
        'type' => 1,
        'user_id' => User::first()->id,
        'order' => Post::count()
    ];
});
