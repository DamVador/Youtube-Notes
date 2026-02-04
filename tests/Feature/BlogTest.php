<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogTest extends TestCase
{
    use RefreshDatabase;

    public function test_blog_index_can_be_rendered(): void
    {
        $response = $this->get('/blog');
        
        $response->assertStatus(200);
    }

    public function test_blog_index_shows_published_posts(): void
    {
        $user = User::factory()->create();
        
        $publishedPost = Post::create([
            'title' => 'Published Post',
            'slug' => 'published-post',
            'content' => 'Content here',
            'is_published' => true,
            'published_at' => now(),
            'user_id' => $user->id,
        ]);
        
        $draftPost = Post::create([
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'content' => 'Draft content',
            'is_published' => false,
            'user_id' => $user->id,
        ]);
        
        $response = $this->get('/blog');
        
        $response->assertStatus(200);
        $response->assertSee('Published Post');
        $response->assertDontSee('Draft Post');
    }

    public function test_blog_post_can_be_viewed(): void
    {
        $user = User::factory()->create();
        
        $post = Post::create([
            'title' => 'Test Post',
            'slug' => 'test-post',
            'content' => '<p>This is the content</p>',
            'excerpt' => 'Short excerpt',
            'is_published' => true,
            'published_at' => now(),
            'user_id' => $user->id,
        ]);
        
        $response = $this->get('/blog/test-post');
        
        $response->assertStatus(200);
        $response->assertSee('Test Post');
        $response->assertSee('This is the content');
    }

    public function test_draft_post_returns_404(): void
    {
        $user = User::factory()->create();
        
        $post = Post::create([
            'title' => 'Draft Post',
            'slug' => 'draft-post',
            'content' => 'Content',
            'is_published' => false,
            'user_id' => $user->id,
        ]);
        
        $response = $this->get('/blog/draft-post');
        
        $response->assertStatus(404);
    }

    public function test_nonexistent_post_returns_404(): void
    {
        $response = $this->get('/blog/nonexistent-post');
        
        $response->assertStatus(404);
    }

    public function test_future_published_post_not_visible(): void
    {
        $user = User::factory()->create();
        
        $post = Post::create([
            'title' => 'Future Post',
            'slug' => 'future-post',
            'content' => 'Content',
            'is_published' => true,
            'published_at' => now()->addDays(7),
            'user_id' => $user->id,
        ]);
        
        $response = $this->get('/blog/future-post');
        
        $response->assertStatus(404);
    }
}