<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Video;
use App\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
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

    public function test_user_can_view_notes_index(): void
    {
        $this->actingAs($this->user)
            ->get(route('notes.index'))
            ->assertStatus(200);
    }

    public function test_user_can_create_a_note(): void
    {
        $noteData = [
            'video_id' => $this->video->id,
            'content' => 'This is a test note',
            'timestamp' => 120,
        ];

        $this->actingAs($this->user)
            ->post(route('notes.store'), $noteData)
            ->assertStatus(200)
            ->assertJson(['content' => 'This is a test note']);

        $this->assertDatabaseHas('notes', [
            'user_id' => $this->user->id,
            'video_id' => $this->video->id,
            'content' => 'This is a test note',
            'timestamp' => 120,
        ]);
    }

    public function test_user_can_create_note_without_timestamp(): void
    {
        $noteData = [
            'video_id' => $this->video->id,
            'content' => 'Note without timestamp',
        ];

        $this->actingAs($this->user)
            ->post(route('notes.store'), $noteData)
            ->assertStatus(200);

        $this->assertDatabaseHas('notes', [
            'content' => 'Note without timestamp',
            'timestamp' => null,
        ]);
    }

    public function test_user_cannot_create_note_on_another_users_video(): void
    {
        $otherUser = User::factory()->create();
        $otherVideo = Video::factory()->create(['user_id' => $otherUser->id]);

        $noteData = [
            'video_id' => $otherVideo->id,
            'content' => 'Sneaky note',
        ];

        $this->actingAs($this->user)
            ->post(route('notes.store'), $noteData)
            ->assertStatus(403);
    }

    public function test_user_can_update_their_own_note(): void
    {
        $note = Note::factory()->create([
            'user_id' => $this->user->id,
            'video_id' => $this->video->id,
        ]);

        $this->actingAs($this->user)
            ->put(route('notes.update', $note), [
                'content' => 'Updated content',
                'timestamp' => 60,
            ])
            ->assertStatus(200)
            ->assertJson(['content' => 'Updated content']);
    }

    public function test_user_cannot_update_another_users_note(): void
    {
        $otherUser = User::factory()->create();
        $otherVideo = Video::factory()->create(['user_id' => $otherUser->id]);
        $note = Note::factory()->create([
            'user_id' => $otherUser->id,
            'video_id' => $otherVideo->id,
        ]);

        $this->actingAs($this->user)
            ->put(route('notes.update', $note), ['content' => 'Hacked!'])
            ->assertStatus(403);
    }

    public function test_user_can_delete_their_own_note(): void
    {
        $note = Note::factory()->create([
            'user_id' => $this->user->id,
            'video_id' => $this->video->id,
        ]);

        $this->actingAs($this->user)
            ->delete(route('notes.destroy', $note))
            ->assertStatus(200);

        $this->assertDatabaseMissing('notes', ['id' => $note->id]);
    }
}