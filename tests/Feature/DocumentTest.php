<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use App\Models\Document;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Video $video;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->video = Video::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_returns_null_when_no_document_exists(): void
    {
        $this->actingAs($this->user)
            ->get(route('documents.show', $this->video))
            ->assertStatus(200);
    }

    public function test_user_can_create_a_document(): void
    {
        $this->actingAs($this->user)
            ->post(route('documents.store', $this->video), [
                'content' => '<p>My notes</p>',
                'content_json' => ['type' => 'doc', 'content' => []],
            ])
            ->assertStatus(200);

        $this->assertDatabaseHas('documents', [
            'user_id' => $this->user->id,
            'video_id' => $this->video->id,
            'content' => '<p>My notes</p>',
        ]);
    }

    public function test_updates_existing_document_instead_of_creating_duplicate(): void
    {
        Document::factory()->create([
            'user_id' => $this->user->id,
            'video_id' => $this->video->id,
            'content' => '<p>Old content</p>',
        ]);

        $this->actingAs($this->user)
            ->post(route('documents.store', $this->video), [
                'content' => '<p>New content</p>',
            ])
            ->assertStatus(200);

        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseHas('documents', [
            'video_id' => $this->video->id,
            'content' => '<p>New content</p>',
        ]);
    }

    public function test_user_cannot_access_another_users_video_document(): void
    {
        $otherUser = User::factory()->create();
        $otherVideo = Video::factory()->create(['user_id' => $otherUser->id]);

        $this->actingAs($this->user)
            ->get(route('documents.show', $otherVideo))
            ->assertStatus(403);
    }
}