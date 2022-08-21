<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use App\User;
use App\Collection;
use App\Post;

class TagTest extends TestCase
{
    use DatabaseMigrations;

    public function testCreateTag()
    {

        $user = factory(User::class)->create();
        $tag_name = \Faker\Factory::create()->word;

        $this->actingAs($user)->json('POST', 'api/tags', [
            'name' => $tag_name,
        ]);

        $this->assertEquals(201, $this->response->status());
        $data = $this->response->getData()->data;
        $this->assertEquals($tag_name, $data->name);
    }

    public function testCreateMultipleTagsAtOnce()
    {
        $user = factory(User::class)->create();
        $tags = [];

        for ($i = 0; $i < 4; $i++) {
            $tags[] = [
                'name' => \Faker\Factory::create()->word
            ];
        }

        $this->actingAs($user)->json('POST', 'api/tags', [
            'tags' => $tags,
        ]);

        $this->assertEquals(201, $this->response->status());
        $data = $this->response->getData()->data;
        $this->assertNotEquals(null, $data);
        $this->assertEquals($tags[0]['name'], $data[0]->name);
    }
}
