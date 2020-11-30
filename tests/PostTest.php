<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;

use App\User;
use App\Collection;

class PostTest extends TestCase
{

    use DatabaseMigrations;

    public function testCreatePost()
    {
        $user = factory(User::class)->create();


        // different content

        $collection = factory(Collection::class)->create();

        $posts = [
            'https://test.com' => 'link',
            'https://test.de' => 'link',
            '<a href="https://www.wolframalpha.com" rel="noopener noreferrer nofollow">https://www.wolframalpha.com</a>' => 'link',
            '<p class="">dfgd adijfds https://google.com</p>' => 'text',
            'Hdfgd fijsdoij <a href="https://slack.com" rel="noopener noreferrer nofollow">https://slack.com</a>' => 'text',
            'https://laravel.com' => 'link',
            'Lorem ipsum https://fonts.adobe.com/fonts/realist' => 'text',
            'https://gamesindustry.biz' => 'link',
            'https://www.php.net/manual/de/function.parse-url.php' => 'link'
        ];

        foreach ($posts as $content => $type) {
            $this->actingAs($user)->json('POST', 'api/posts', [
                'content' => $content,
                'collection_id' => $collection->id
            ]);
            // echo $this->response->getData();
            $this->assertEquals(201, $this->response->status());
            $data = $this->response->getData()->data;
            $this->assertEquals($type, $data->type);
            if ($data->type === 'link') {
                $this->assertNotEquals(null, $data->color);
                $this->assertNotEquals(null, $data->url);
            }
            $this->assertNotEquals(null, $data->collection_id);
        }

        // different collections

        $collections = [0, null];

        foreach ($collections as &$collection) {
            $this->actingAs($user)->json('POST', 'api/posts', [
                'content' => 'Test blab bla foo',
                'collection_id' => $collection
            ]);
            $this->assertEquals(201, $this->response->status());
            $data = $this->response->getData()->data;
        }

        // no collection

        $this->actingAs($user)->json('POST', 'api/posts', [
            'content' => 'Test blab bla foo'
        ]);
        $this->assertEquals(201, $this->response->status());
        $data = $this->response->getData()->data;


    }
}
