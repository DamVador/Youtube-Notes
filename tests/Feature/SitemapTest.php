<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_returns_xml(): void
    {
        $response = $this->get('/sitemap.xml');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_contains_static_pages(): void
    {
        $response = $this->get('/sitemap.xml');
        $content = $response->getContent();
        
        $response->assertStatus(200);
        $this->assertStringContainsString(url('/'), $content);
        $this->assertStringContainsString(url('/features'), $content);
        $this->assertStringContainsString(url('/blog'), $content);
    }

    public function test_sitemap_contains_published_posts(): void
    {
        $user = User::factory()->create();
        
        $publishedPost = Post::create([
            'title' => 'Published Post',
            'slug' => 'published-post',
            'content' => 'Content',
            'is_published' => true,
            'published_at' => now(),
            'user_id' => $user->id,
        ]);
        
        $response = $this->get('/sitemap.xml');
        $content = $response->getContent();
        
        $response->assertStatus(200);
        $this->assertStringContainsString('/blog/published-post', $content);
    }

    public function test_sitemap_excludes_draft_posts(): void
    {
        $user = User::factory()->create();
        
        $draftPost = Post::create([
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'content' => 'Content',
            'is_published' => false,
            'user_id' => $user->id,
        ]);
        
        $response = $this->get('/sitemap.xml');
        $content = $response->getContent();
        
        $response->assertStatus(200);
        $this->assertStringNotContainsString('/blog/draft-post', $content);
    }

    public function test_sitemap_is_valid_xml(): void
    {
        $user = User::factory()->create();
        
        Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => 'Content',
            'is_published' => true,
            'published_at' => now(),
            'user_id' => $user->id,
        ]);
        
        $response = $this->get('/sitemap.xml');
        
        $xml = simplexml_load_string($response->getContent());
        
        $this->assertNotFalse($xml);
        $this->assertEquals('urlset', $xml->getName());
    }
}