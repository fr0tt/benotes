<?php

namespace Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use App\Models\Post;
use App\Models\Collection;

class ImAndExportTest extends TestCase
{

    use RefreshDatabase;

    public function testImportBookmarks()
    {

        Config::set('benotes.generate_missing_thumbnails', false);
        $this->assertFalse(config('benotes.generate_missing_thumbnails'));

        $user = User::factory()->create();

        $response = $this->actingAs($user)->json('POST', 'api/imports', [
            'file' => new UploadedFile(
                base_path('tests/bookmarks.html'),
                'bookmarks.html',
                'text/html',
                null,
                true
            )
        ]);
        $response->assertStatus(201);
        $this->assertEquals(7, Post::count());
        $this->assertEquals(8, Collection::count());

        $collection = Collection::where([
            'name'    => Collection::IMPORTED_COLLECTION_NAME,
            'user_id' => $user->id
        ])->first();
        $this->assertTrue($collection->exists());

        $personal = Collection::where([
            'name'      => 'Personal Toolbar',
            'parent_id' => $collection->id
        ])->first();
        $this->assertTrue($personal->exists());

        $collectionA = Collection::where([
            'name'      => 'CollectionA',
            'parent_id' => $personal->id
        ])->first();
        $this->assertTrue($collectionA->exists());

        $collectionA1 = Collection::where([
            'name'      => 'SubCollectionA1',
            'parent_id' => $collectionA->id
        ])->first();
        $this->assertTrue($collectionA1->exists());

        $collectionA1a = Collection::where([
            'name'      => 'SubSubCollectionA1a',
            'parent_id' => $collectionA1->id
        ])->first();
        $this->assertTrue($collectionA1a->exists());

        $collectionA2 = Collection::where([
            'name'      => 'SubCollectionA2',
            'parent_id' => $collectionA->id
        ])->first();
        $this->assertTrue($collectionA2->exists());

        $collectionB = Collection::where([
            'name'      => 'CollectionB',
            'parent_id' => $personal->id
        ])->first();
        $this->assertTrue($collectionB->exists());

        $this->assertTrue(Post::where([
            'content'       => 'https://www.mozilla.org/en-US/',
            'collection_id' => $personal->id
        ])->exists());
        $this->assertTrue(Post::where([
            'content'       => 'https://www.bbc.com/',
            'collection_id' => $collectionA->id
        ])->exists());
        $this->assertTrue(Post::where([
            'content'       => 'https://www.theguardian.com/international',
            'collection_id' => $collectionA1->id
        ])->exists());
        $this->assertTrue(Post::where([
            'content'       => 'https://www.nytimes.com/',
            'collection_id' => $collectionA1a->id
        ])->exists());
        $this->assertTrue(Post::where([
            'content'       => 'https://www.washingtonpost.com/',
            'collection_id' => $collectionA2->id
        ])->exists());
        $this->assertTrue(Post::where([
            'content'       => 'https://attrakdiff.de/index-en.html',
            'collection_id' => $collectionB->id
        ])->exists());

    }

    public function testExportBookmark()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $post = Post::factory()->create([
            'content'       => 'https://gitlab.com',
            'url'           => 'https://gitlab.com',
            'base_url'      => 'https://gitlab.com',
            'type'          => Post::POST_TYPE_LINK,
            'collection_id' => $collection->id
        ]);

        $response = $this->actingAs($user)->json('GET', 'api/exports');
        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $export = file_get_contents($response->getFile());
        $this->assertStringContainsString('<DT><H3', $export);
        $this->assertStringContainsString('>' . $collection->name, $export);
        $this->assertStringContainsString('<DT><A', $export);

    }

    public function testExportBookmarkWithoutCollection()
    {
        $user = User::factory()->create();
        $collection = Collection::factory()->create();
        $post = Post::factory()->create([
            'content'       => 'https://gitlab.com',
            'url'           => 'https://gitlab.com',
            'base_url'      => 'https://gitlab.com',
            'type'          => Post::POST_TYPE_LINK,
            'collection_id' => null
        ]);

        $response = $this->actingAs($user)->json('GET', 'api/exports');
        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $export = file_get_contents($response->getFile());
        $this->assertStringContainsString('<DT><H3', $export);
        $this->assertStringContainsString('>Uncategorized</H3>', $export);
        $this->assertStringContainsString('>' . $collection->name . '</H3>', $export);
        $this->assertStringContainsString('<DT><A', $export);
        $this->assertStringContainsString('>' . $post->title . '</A>', $export);

    }

    public function testExportBookmarks()
    {

        Config::set('benotes.generate_missing_thumbnails', false);
        $this->assertFalse(config('benotes.generate_missing_thumbnails'));
        $user = User::factory()->create();

        $response = $this->actingAs($user)->json('POST', 'api/imports', [
            'file' => new UploadedFile(
                base_path('tests/bookmarks.html'),
                'bookmarks.html',
                'text/html',
                null,
                true
            )
        ]);

        $response->assertStatus(201);
        $this->assertEquals(7, Post::count());
        $this->assertEquals(8, Collection::count());

        $response = $this->actingAs($user)->json('GET', 'api/exports');
        $response->assertStatus(200);
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));

        $exported = file_get_contents($response->getFile());
        $original = file_get_contents(base_path('tests/bookmarks.html'));

        $original = strip_tags($original, ['dl', 'p', 'dt', 'h3', 'a', 'dd']);
        // $original = preg_replace('/(^.*(?:\n.*))*?<DL>/m', '<DL>', $original, 1);
        // remove attributes
        $original = preg_replace('/(\s*\w+=".*"\s*)|(\s+)/m', '', $original);

        // remove collection 'Imported Bookmarks' because it doesn't exist in the original
        $exported = preg_replace(
            '/<DT><H3.+>' . Collection::IMPORTED_COLLECTION_NAME . '<\/H3>\s+<DL><p>/',
            '',
            $exported
        );
        $exported = Str::replaceLast('</DL><p>', '', $exported);
        $exported = strip_tags($exported, ['dl', 'p', 'dt', 'h3', 'a', 'dd']);
        // remove descriptions (and attributes) because they don't exist in the original
        $exported = preg_replace('/<DD>\X*?</', '<', $exported);
        $exported = preg_replace('/(\s*\w+=".*"\s*)|(\s+)/m', '', $exported);
        // there is no last <p> in Firefox
        $exported = Str::replaceLast('<p>', '', $exported);

        $this->assertEquals($original, $exported);

    }

}
