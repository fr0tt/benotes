<?php

namespace Tests\Feature;

use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;
use App\Models\User;

class TagTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateTag()
    {

        $user = User::factory()->create();
        $tag_name = $this->faker->word();

        $response = $this->actingAs($user)->json('POST', 'api/tags', [
            'name' => $tag_name,
        ]);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
        $this->assertEquals($tag_name, $data->name);
    }

    public function testCreateMultipleTagsAtOnce()
    {
        $user = User::factory()->create();
        $tags = [];

        for ($i = 0; $i < 4; $i++) {
            $tags[] = [
                'name' => $this->faker->word()
            ];
        }

        $response = $this->actingAs($user)->json('POST', 'api/tags', [
            'tags' => $tags,
        ]);

        $this->assertEquals(201, $response->status());
        $data = $response->getData()->data;
        $this->assertNotEquals(null, $data);
        $this->assertEquals($tags[0]['name'], $data[0]->name);
    }

    public function testDeleteTag()
    {

        $user = User::factory()->create();
        $tag = Tag::factory()->create([
            'user_id' => $user->id
        ]);

        $response = $this->actingAs($user)->json('DELETE', 'api/tags/' . $tag->id);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->status());
        $this->assertEquals(0, Tag::count());
    }

}
