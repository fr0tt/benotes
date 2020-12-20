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

        $collection = factory(Collection::class)->create();

        $posts = [
            'https://test.com' => 'link',
            'https://test.de' => 'link',
            'https://www.amazon.com/Design-Everyday-Things-Revised-Expanded/dp/0465050654/ref=sr_1_1?dchild=1&keywords=don+norman&link_code=qs&qid=1608495907&sr=8-1&tag=operabrowser-21' => 'link',
            '<a href="https://www.wolframalpha.com" rel="noopener noreferrer nofollow">https://www.wolframalpha.com</a>' => 'link',
            '<p class="">dfgd adijfds https://google.com</p>' => 'text',
            'Hdfgd fijsdoij <a href="https://slack.com" rel="noopener noreferrer nofollow">https://slack.com</a>' => 'text',
            'https://laravel.com' => 'link',
            'Lorem ipsum https://fonts.adobe.com/fonts/realist' => 'text',
            'https://gamesindustry.biz' => 'link',
            'https://www.php.net/manual/en/function.parse-url.php' => 'link'
        ];

        foreach ($posts as $content => $type) {
            $this->actingAs($user)->json('POST', 'api/posts', [
                'content' => $content,
                'collection_id' => $collection->id
            ]);

            //echo var_dump($this->response->getData()->data);

            $this->assertEquals(201, $this->response->status());
            $data = $this->response->getData()->data;
            $this->assertEquals($type, $data->type);
            if ($data->type === 'link') {
                $this->assertNotEquals(null, $data->color);
                $this->assertNotEquals(null, $data->url);
            }
            $this->assertNotEquals(null, $data->collection_id);
        }

    }

    public function testCreatePostWithDifferentCollections()
    {

        $user = factory(User::class)->create();
        $collection = factory(Collection::class)->create();

        $posts = [
            [
                'content' => 'foo bar',
                'collection_id' => $collection->id
            ],
            [
                'content' => 'foo bar',
                'is_uncategorized' => true
            ],
            [
                'content' => 'foo bar',
                'is_uncategorized' => false
            ],
            [
                'content' => 'foo bar'
            ]
        ];

        foreach ($posts as &$post) {
            $this->actingAs($user)->json('POST', 'api/posts', $post);
            $this->assertEquals(201, $this->response->status());
            $data = $this->response->getData()->data;
            $this->assertNotEquals($data, null);
        }
    }

}
