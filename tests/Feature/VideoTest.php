<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VideoTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_authenticated_user_can_view_videos_index(): void
    {
        $this->actingAs($this->user)
            ->get(route('videos.index'))
            ->assertStatus(200);
    }

    public function test_guest_is_redirected_from_videos_index(): void
    {
        $this->get(route('videos.index'))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_save_a_video(): void
    {
        $videoData = [
            'youtube_id' => 'dQw4w9WgXcQ',
            'title' => 'Test Video',
            'thumbnail' => 'https://example.com/thumb.jpg',
            'channel_name' => 'Test Channel',
        ];

        $this->actingAs($this->user)
            ->post(route('videos.store'), $videoData)
            ->assertStatus(200)
            ->assertJson(['youtube_id' => 'dQw4w9WgXcQ']);

        $this->assertDatabaseHas('videos', [
            'user_id' => $this->user->id,
            'youtube_id' => 'dQw4w9WgXcQ',
        ]);
    }

    public function test_user_can_view_their_own_video(): void
    {
        $video = Video::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->get(route('videos.show', $video))
            ->assertStatus(200);
    }

    public function test_user_cannot_view_another_users_video(): void
    {
        $otherUser = User::factory()->create();
        $video = Video::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user)
            ->get(route('videos.show', $video))
            ->assertStatus(403);
    }

    public function test_user_can_delete_their_own_video(): void
    {
        $video = Video::factory()->create(['user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->delete(route('videos.destroy', $video))
            ->assertRedirect(route('videos.index'));

        $this->assertDatabaseMissing('videos', ['id' => $video->id]);
    }

    public function test_user_cannot_delete_another_users_video(): void
    {
        $otherUser = User::factory()->create();
        $video = Video::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user)
            ->delete(route('videos.destroy', $video))
            ->assertStatus(403);
    }
}