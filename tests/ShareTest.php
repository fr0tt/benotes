<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Faker\Generator as Faker;
use App\User;
use App\Collection;

class ShareTest extends TestCase
{

    use DatabaseMigrations;

    public function testCreateShare()
    {

        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create();
        $token = \Faker\Factory::create()->slug;

        $this->actingAs($user)->json('POST', 'api/shares', [
            'token' => $token,
            'collection_id' => $collection->id,
            'is_active' => true
        ]);

        $this->assertEquals(201, $this->response->status());


        $this->actingAs($user)->json('GET', 's?token=' . $token);
        $this->assertEquals(200, $this->response->status());

    }

}