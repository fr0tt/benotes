<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

use App\Post;
use App\Collection;

use ColorThief\ColorThief;

class PostController extends Controller
{

    public function index(Request $request)
    {

        $this->validate($request, [
            'collection_id' => 'integer|nullable',
            'limit' => 'integer'
        ]);
        $request->is_uncategorized = filter_var($request->is_uncategorized, FILTER_VALIDATE_BOOLEAN);

        if (isset($request->collection_id)) {
            $posts = Post::where([
                ['collection_id', '=', $request->collection_id],
                ['user_id', '=', Auth::user()->id]
            ]);
        } else if ($request->is_uncategorized === true) {
            $posts = Post::where([
                ['collection_id', '=', null],
                ['user_id', '=', Auth::user()->id]
            ]);
        } else {
            $posts = Post::where('user_id', Auth::user()->id);
        }

        if (isset($request->limit)) {
            $posts = $posts->limit($request->limit);
        }
        $posts = $posts->get();

        return response()->json(['data' => $posts], 200);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'content' => 'required|string',
            'collection_id' => 'integer|nullable' 
        ]);

        if (isset($request->collection_id)) {
            $collection = Collection::find($request->collection_id);
            if (!$collection) {
                return response()->json('Collection not found.', 404);
            }
        }

        $info = $this->computePostData($request->content);
        
        if ($info['type'] === Post::POST_TYPE_LINK) {
            $post = Post::create([
                'content' => $request->content,
                'type' => $info['type'],
                'url' => $info['url'],
                'base_url' => $info['base_url'],
                'title' => $info['title'],
                'description' => $info['description'],
                'color' => $info['color'],
                'collection_id' => $request->collection_id,
                'user_id' => Auth::user()->id
            ]);
            $this->saveImage($info['image_path'], $post);
        } else {
            $post = Post::create([
                'content' => $request->content,
                'type' => $info['type'],
                'collection_id' => $request->collection_id,
                'user_id' => Auth::user()->id
            ]);
        }

        return response()->json(['data' => $post], 201);
    }

    public function update(Request $request, $id)
    {

        $this->validate($request, [
            'content' => 'required|string',
            'collection_id' => 'integer|nullable'
        ]);

        $post = Post::find($id);
        if (!$post) {
            return response()->json('Post not found.', 404);
        }

        if (isset($request->collection_id)) {
            $collection = Collection::find($request->collection_id);
            if (!$collection) {
                return response()->json('Collection not found.', 404);
            }
        }

        $info = $this->computePostData($request->content);

        if ($info['type'] === Post::POST_TYPE_LINK) {
            $post->update([
                'content' => $request->content,
                'type' => $info['type'],
                'url' => $info['url'],
                'base_url' => $info['base_url'],
                'title' => $info['title'],
                'description' => $info['description'],
                'color' => $info['color'],
                'collection_id' => $request->collection_id
            ]);
            $this->saveImage($info['image_path'], $post);
        } else {
            $post->update([
                'content' => $request->content,
                'type' => $info['type'],
                'collection_id' => $request->collection_id,
                'user_id' => Auth::user()->id
            ]);
        }

        return response()->json(['data' => $post], 200);
    }

    public function destroy($id)
    {

        $post = Post::find($id);
        if (!$post) {
            return response()->json('Post not found.', 404);
        }
        $post->delete();

        return response()->json('', 204);

    }

    private function computePostData($content)
    {
        preg_match_all('/(https|http)(:\/\/)(\w+\.)+(\w+)(\S+)/', $content, $matches);
        $matches = $matches[0];
        $info = null;
        if (count($matches) > 0) {
            $info = $this->getInfo($matches[0]);
        }

        if (empty($matches)) {
            $info['type'] = Post::POST_TYPE_TEXT;
        } else if (strlen($content) > strlen($matches[0])) {
            $info['type'] = Post::POST_TYPE_TEXT;
        } else {
            $info['type'] = Post::POST_TYPE_LINK;
        }

        return $info;
    }

    private function getInfo($url)
    {

        $base_url = parse_url($url);
        $base_url = $base_url['scheme'] . '://' . $base_url['host'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $html = curl_exec($ch);
        curl_close($ch);

        $document = new \DOMDocument();
        @$document->loadHTML($html);
        $titles = $document->getElementsByTagName('title');
        $title = trim($titles->item(0)->nodeValue); 
        $metas = $document->getElementsByTagName('meta');
        for ($i = 0; $i < $metas->length; $i++) {
            $meta = $metas->item($i);
            if ($meta->getAttribute('name') === 'description') {
                $description = $meta->getAttribute('content');
            } else if ($meta->getAttribute('name') === 'theme-color') {
                $color = $meta->getAttribute('content');
            } else if ($meta->getAttribute('property') === 'og:image') {
                $image = $meta->getAttribute('content');
            }
        }

        if (empty($color)) {
            $color = $this->getDominantColor($base_url);
        }

        return [
            'url' => $url,
            'base_url' => $base_url,
            'title' => $title,
            'description' => (empty($description)) ? null : $description,
            'color' => (empty($color)) ? null : $color,
            'image_path' => (empty($image)) ? null : $image,
        ];
    }

    private function getDominantColor($base_url)
    {
        $rgb = ColorThief::getColor('http://www.google.com/s2/favicons?domain=' . $base_url);
        $hex = sprintf("#%02x%02x%02x", $rgb[0], $rgb[1], $rgb[2]);
        return $hex;
    }

    private function saveImage($image_path, $post)
    {
        if (!empty($image_path)) {
            $image = Image::make($image_path);
            if ($image) {
                $image->fit(400, 210);
                $filename = 'thumbnail_' . $post->id . '.jpg';
                $image->save('../storage/app/public/thumbnails/' . $filename, 100);
                if (empty($post->image_path)) {
                    $post->image_path = $filename;
                    $post->save();
                }
            }
        }
    }

}
