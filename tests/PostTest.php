<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

use App\User;
use App\Collection;

class PostTest extends TestCase
{
    public function testPost()
    {
        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create();

        $contents = [
            'https://laravel.com',
            'Lorem ipsum https://fonts.adobe.com/fonts/realist'
        ];

        foreach ($contents as &$content) {
            $this->actingAs($user)->json('POST', 'api/posts', [
                'content' => $content,
                'collection_id' => $collection->id
            ]);
            $this->assertEquals(201, $this->response->status());
            $data = $this->response->getData()->data;
            if ($data->type === 'link') {
                $this->assertNotEquals(null, $data->color);
                $this->assertNotEquals(null, $data->url);
            }
            $this->assertNotEquals(null, $data->collection_id);
        }

    }
}
