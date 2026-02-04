<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPostTest extends TestCase
{
    use RefreshDatabase;

    private function createAdminUser(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    private function createRegularUser(): User
    {
        return User::factory()->create(['is_admin' => false]);
    }

    public function test_guest_cannot_access_admin_posts(): void
    {
        $response = $this->get(route('admin.posts.index'));
        
        $response->assertRedirect(route('login'));
    }

    public function test_regular_user_cannot_access_admin_posts(): void
    {
        $user = $this->createRegularUser();
        
        $response = $this->actingAs($user)->get(route('admin.posts.index'));
        
        $response->assertStatus(403);
    }

    public function test_admin_can_view_posts_index(): void
    {
        $admin = $this->createAdminUser();
        
        $response = $this->actingAs($admin)->get(route('admin.posts.index'));
        
        $response->assertStatus(200);
    }

    public function test_admin_can_view_create_form(): void
    {
        $admin = $this->createAdminUser();
        
        $response = $this->actingAs($admin)->get(route('admin.posts.create'));
        
        $response->assertStatus(200);
    }

    public function test_admin_can_create_post(): void
    {
        $admin = $this->createAdminUser();
        
        $response = $this->actingAs($admin)->post(route('admin.posts.store'), [
            'title' => 'New Blog Post',
            'slug' => 'new-blog-post',
            'content' => 'This is the content of the blog post.',
            'excerpt' => 'Short excerpt',
            'is_published' => true,
        ]);
        
        $response->assertRedirect(route('admin.posts.index'));
        
        $this->assertDatabaseHas('posts', [
            'title' => 'New Blog Post',
            'slug' => 'new-blog-post',
            'user_id' => $admin->id,
        ]);
    }

    public function test_admin_can_create_post_without_slug(): void
    {
        $admin = $this->createAdminUser();
        
        $response = $this->actingAs($admin)->post(route('admin.posts.store'), [
            'title' => 'Auto Slug Post',
            'content' => 'Content here',
            'is_published' => false,
        ]);
        
        $response->assertRedirect(route('admin.posts.index'));
        
        $this->assertDatabaseHas('posts', [
            'title' => 'Auto Slug Post',
            'slug' => 'auto-slug-post',
        ]);
    }

    public function test_admin_can_edit_post(): void
    {
        $admin = $this->createAdminUser();
        
        $post = Post::create([
            'title' => 'Original Title',
            'slug' => 'original-title',
            'content' => 'Original content',
            'user_id' => $admin->id,
        ]);
        
        $response = $this->actingAs($admin)->get(route('admin.posts.edit', $post));
        
        $response->assertStatus(200);
    }

    public function test_admin_can_update_post(): void
    {
        $admin = $this->createAdminUser();
        
        $post = Post::create([
            'title' => 'Original Title',
            'slug' => 'original-title',
            'content' => 'Original content',
            'user_id' => $admin->id,
        ]);
        
        $response = $this->actingAs($admin)->put(route('admin.posts.update', $post), [
            'title' => 'Updated Title',
            'slug' => 'updated-title',
            'content' => 'Updated content',
            'is_published' => true,
        ]);
        
        $response->assertRedirect(route('admin.posts.index'));
        
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'title' => 'Updated Title',
            'slug' => 'updated-title',
        ]);
    }

    public function test_admin_can_delete_post(): void
    {
        $admin = $this->createAdminUser();
        
        $post = Post::create([
            'title' => 'To Delete',
            'slug' => 'to-delete',
            'content' => 'Content',
            'user_id' => $admin->id,
        ]);
        
        $response = $this->actingAs($admin)->delete(route('admin.posts.destroy', $post));
        
        $response->assertRedirect(route('admin.posts.index'));
        
        $this->assertDatabaseMissing('posts', [
            'id' => $post->id,
        ]);
    }

    public function test_published_at_is_set_when_publishing(): void
    {
        $admin = $this->createAdminUser();
        
        $response = $this->actingAs($admin)->post(route('admin.posts.store'), [
            'title' => 'Published Post',
            'content' => 'Content',
            'is_published' => true,
        ]);
        
        $post = Post::where('title', 'Published Post')->first();
        
        $this->assertNotNull($post->published_at);
    }

    public function test_published_at_not_set_for_draft(): void
    {
        $admin = $this->createAdminUser();
        
        $response = $this->actingAs($admin)->post(route('admin.posts.store'), [
            'title' => 'Draft Post',
            'content' => 'Content',
            'is_published' => false,
        ]);
        
        $post = Post::where('title', 'Draft Post')->first();
        
        $this->assertNull($post->published_at);
    }

    public function test_title_is_required(): void
    {
        $admin = $this->createAdminUser();
        
        $response = $this->actingAs($admin)->post(route('admin.posts.store'), [
            'content' => 'Content without title',
        ]);
        
        $response->assertSessionHasErrors('title');
    }

    public function test_content_is_required(): void
    {
        $admin = $this->createAdminUser();
        
        $response = $this->actingAs($admin)->post(route('admin.posts.store'), [
            'title' => 'Title without content',
        ]);
        
        $response->assertSessionHasErrors('content');
    }

    public function test_slug_must_be_unique(): void
    {
        $admin = $this->createAdminUser();
        
        Post::create([
            'title' => 'Existing Post',
            'slug' => 'existing-slug',
            'content' => 'Content',
            'user_id' => $admin->id,
        ]);
        
        $response = $this->actingAs($admin)->post(route('admin.posts.store'), [
            'title' => 'New Post',
            'slug' => 'existing-slug',
            'content' => 'Content',
        ]);
        
        $response->assertSessionHasErrors('slug');
    }
}