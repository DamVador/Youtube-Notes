<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Tag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_their_tags(): void
    {
        Tag::factory()->count(3)->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->get(route('tags.index'))
            ->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_only_returns_users_own_tags(): void
    {
        Tag::factory()->count(2)->create(['user_id' => $this->user->id]);

        $otherUser = User::factory()->create();
        Tag::factory()->count(3)->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user)
            ->get(route('tags.index'))
            ->assertStatus(200)
            ->assertJsonCount(2);
    }

    public function test_user_can_create_a_tag(): void
    {
        $this->actingAs($this->user)
            ->post(route('tags.store'), [
                'name' => 'Important',
                'color' => '#ef4444',
            ])
            ->assertStatus(200)
            ->assertJson([
                'name' => 'Important',
                'color' => '#ef4444',
            ]);

        $this->assertDatabaseHas('tags', [
            'user_id' => $this->user->id,
            'name' => 'Important',
        ]);
    }

    public function test_uses_default_color_when_not_provided(): void
    {
        $this->actingAs($this->user)
            ->post(route('tags.store'), [
                'name' => 'Default Color Tag',
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('tags', [
            'name' => 'Default Color Tag',
            'color' => '#3B82F6',
        ]);
    }

    public function test_user_can_update_their_own_tag(): void
    {
        $tag = Tag::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->put(route('tags.update', $tag), [
                'name' => 'Updated Name',
                'color' => '#22c55e',
            ])
            ->assertStatus(200)
            ->assertJson(['name' => 'Updated Name']);
    }

    public function test_user_cannot_update_another_users_tag(): void
    {
        $otherUser = User::factory()->create();
        $tag = Tag::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user)
            ->put(route('tags.update', $tag), ['name' => 'Hacked!'])
            ->assertStatus(403);
    }

    public function test_user_can_delete_their_own_tag(): void
    {
        $tag = Tag::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->delete(route('tags.destroy', $tag))
            ->assertStatus(200);

        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }
}