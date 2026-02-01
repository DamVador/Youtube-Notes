<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function index(): Response
    {
        // Pages statiques
        $staticPages = [
            ['url' => url('/'), 'lastmod' => '2025-01-29', 'changefreq' => 'weekly', 'priority' => '1.0'],
            ['url' => url('/features'), 'lastmod' => '2025-01-29', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['url' => url('/pricing'), 'lastmod' => '2025-01-29', 'changefreq' => 'monthly', 'priority' => '0.8'],
            ['url' => url('/blog'), 'lastmod' => now()->toDateString(), 'changefreq' => 'weekly', 'priority' => '0.7'],
            ['url' => url('/login'), 'lastmod' => '2025-01-29', 'changefreq' => 'monthly', 'priority' => '0.5'],
            ['url' => url('/register'), 'lastmod' => '2025-01-29', 'changefreq' => 'monthly', 'priority' => '0.5'],
        ];

        // Articles de blog publiés
        $posts = Post::published()
            ->select('slug', 'updated_at')
            ->orderBy('published_at', 'desc')
            ->get()
            ->map(fn($post) => [
                'url' => url('/blog/' . $post->slug),
                'lastmod' => $post->updated_at->toDateString(),
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ])
            ->toArray();

        $pages = array_merge($staticPages, $posts);

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ($pages as $page) {
            $xml .= '    <url>' . "\n";
            $xml .= '        <loc>' . $page['url'] . '</loc>' . "\n";
            $xml .= '        <lastmod>' . $page['lastmod'] . '</lastmod>' . "\n";
            $xml .= '        <changefreq>' . $page['changefreq'] . '</changefreq>' . "\n";
            $xml .= '        <priority>' . $page['priority'] . '</priority>' . "\n";
            $xml .= '    </url>' . "\n";
        }

        $xml .= '</urlset>';

        return response($xml)->header('Content-Type', 'application/xml');
    }
}