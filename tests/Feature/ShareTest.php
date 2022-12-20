<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Collection;

class ShareTest extends TestCase
{

    use RefreshDatabase;

    public function testCreateShare()
    {

        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $token = $this->faker->slug();

        $response = $this->actingAs($user)->json('POST', 'api/shares', [
            'token' => $token,
            'collection_id' => $collection->id,
            'is_active' => true
        ]);

        $this->assertEquals(201, $response->status());


        $response = $this->actingAs($user)->json('GET', 's?token=' . $token);
        $this->assertEquals(200, $response->status());
    }
}
