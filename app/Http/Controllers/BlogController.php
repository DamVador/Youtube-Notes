<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Inertia\Inertia;

class BlogController extends Controller
{
    public function index()
    {
        $posts = Post::published()
            ->with('author')
            ->orderBy('published_at', 'desc')
            ->paginate(10);

        return Inertia::render('Blog/Index', [
            'posts' => $posts,
            'canLogin' => route_exists('login'),
            'canRegister' => route_exists('register'),
        ]);
    }

    public function show(string $slug)
    {
        $post = Post::published()
            ->where('slug', $slug)
            ->with('author')
            ->firstOrFail();

        $relatedPosts = Post::published()
            ->where('id', '!=', $post->id)
            ->orderBy('published_at', 'desc')
            ->limit(3)
            ->get();

        return Inertia::render('Blog/Show', [
            'post' => $post,
            'relatedPosts' => $relatedPosts,
            'canLogin' => route_exists('login'),
            'canRegister' => route_exists('register'),
        ]);
    }
}

// Helper function if not exists
if (!function_exists('route_exists')) {
    function route_exists($name) {
        return app('router')->has($name);
    }
}