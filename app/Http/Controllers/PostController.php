<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;

use ColorThief\ColorThief;

class PostController extends Controller
{

    public function index()
    {
        $posts = Post::all();
        return response()->json(['data' => $posts]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'content' => 'required|string',
        ]);

        preg_match_all('/(https|http)(:\/\/)(\w+\.)+(\w+)/', $request->content, $matches);
        $matches = $matches[0];
        if (count($matches) > 0) {
            $info = $this->getInfo($matches[0]);
        }
        if ($request->content > strlen($matches[0])) {
            $type = Post::POST_TYPE_TEXT;
        } else {
            $type = Post::POST_TYPE_TEXT;
        }

        $post = Post::create([
            'content' => $request->content,
            'type' => $type,
            'base_url' => $info['base_url'],
            'title' => $info['title'],
            'description' => $info['description'],
            'color' => $info['color'],
            'image_path' => $info['image_path']
        ]);
        
        return response()->json(['data' => $post], 201);
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
        $title = $titles->item(0)->nodeValue;
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

        if (!empty($color)) {
            $color = $this->getDominantColor($base_url);
        }

        return [
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

}
