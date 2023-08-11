<?php

namespace App\Helpers;

use App\Models\Collection;
use App\Models\Post;
use App\Services\PostService;
use Illuminate\Support\Facades\Cache;

class NetscapeBookmarkEncoder
{
    private PostService $postService;
    private $user_id;

    public function __construct(int $user_id)
    {
        $this->postService = new PostService();
        $this->user_id = $user_id;
    }

    public function encodeToFile($filepath)
    {
        $content = $this->serialize();
        file_put_contents($filepath, $content);
    }

    private function serialize(): string
    {
        $content = "<!DOCTYPE NETSCAPE-Bookmark-file-1>\n" .
            "<!-- This is an automatically generated file by Benotes. -->\n" .
            "<META HTTP-EQUIV=\"Content-Type\" CONTENT=\"text/html; charset=UTF-8\">\n" .
            "<TITLE>Bookmarks</TITLE>\n" .
            "<H1>Bookmarks</H1>\n" .
            "\n" .
            "<DL><p>\n";

        $collections = Collection::where('user_id', $this->user_id)
            ->whereNull('parent_id')
            ->with('nested')
            ->orderBy('name')
            ->get();

        if (
            Post::where('user_id', $this->user_id)
                ->whereNull('collection_id')
                ->where('type', Post::POST_TYPE_LINK)
                ->exists()
        ) {
            $collections->prepend((object) ['name' => 'Uncategorized', 'id' => null]);
        }

        $content .= $this->collectionsRecursive($collections, 0);

        return $content .= "</DL><p>";
    }

    private function collectionsRecursive($collections, $level): string
    {
        $content = "";
        $level++;
        foreach ($collections as $collection) {

            $dates = Post::select('created_at', 'updated_at')
                ->where('collection_id', $collection->id)
                ->latest()
                ->first();

            if (empty($dates)) {
                // there is no data that could be used
                $dates = [
                    'created_at' => '',
                    'updated_at' => ''
                ];
            } else {
                $dates = $dates->only('created_at', 'updated_at');
                $dates['created_at'] = strtotime($dates['created_at']);
                $dates['updated_at'] = strtotime($dates['updated_at']);
            }

            $content .= "{$this->tabs($level)}<DT><H3 " .
                "ADD_DATE=\"{$dates['created_at']}\" " .
                "LAST_MODIFIED=\"{$dates['updated_at']}\">" .
                "{$collection->name}</H3>\n";

            $content .= $this->tabs($level) . "<DL><p>\n";
            $content .= $this->getPosts($collection->id, $level + 1);

            if (!empty($collection->nested)) {
                $content .= $this->collectionsRecursive($collection->nested, $level);
            }
            $content .= $this->tabs($level) . "</DL><p>\n";

        }
        return $content;
    }

    private function getPosts(int $collectionId = null, int $level): string
    {
        $content = "";
        $posts = Post::where('collection_id', $collectionId)
            ->where('type', Post::POST_TYPE_LINK)
            ->with('tags')
            ->orderBy('order', 'desc')
            ->get();

        foreach ($posts as $post) {
            $createdAt = strtotime($post->created_at);
            $updatedAt = strtotime($post->updated_at);
            $description = trim($post->description);
            $tags = $post->tags->implode('name', ', ');

            $content .= "{$this->tabs($level)}" .
                "<DT><A HREF=\"{$post->url}\" " .
                "ADD_DATE=\"{$createdAt}\" " .
                "LAST_MODIFIED=\"{$updatedAt}\" " .
                "ICON=\"{$this->icon($post->base_url)}\" " .
                "TAGS=\"{$tags}\"" .
                ">{$post->title}</A>\n" .
                "{$this->tabs($level)}" .
                "<DD>{$description}\n";
        }
        return $content;
    }

    private function tabs($level): string
    {
        return str_repeat("\t", $level);
    }

    private function icon(string $baseUrl): string
    {
        $cacheKey = 'icon:' . $baseUrl;
        if (Cache::has($cacheKey)) {
            $icon = Cache::get($cacheKey);
        } else {
            $url = 'https://t2.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=' . $baseUrl . '&size=16';
            $icon = @file_get_contents($url);
            Cache::put($cacheKey, $icon ? $icon : null);
        }
        if (!$icon || empty($icon) || $icon === '') {
            return '';
        }
        return 'data:image/png;base64,' . base64_encode($icon);
    }

}
