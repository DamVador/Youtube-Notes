<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Cashier\Billable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id',
        'avatar',
        'is_admin',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'is_admin' => 'boolean',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(Tag::class);
    }
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Check if user has premium subscription
     */
    public function isPremium(): bool
    {
        return 
           $this->subscriptions()->where('stripe_status', 'active')->exists();
    }

    /**
     * Get the maximum number of videos allowed
     */
    public function maxVideos(): int
    {
        return $this->isPremium() 
            ? config('limits.premium.videos') 
            : config('limits.free.videos');
    }

    /**
     * Get the maximum number of quick notes per video
     */
    public function maxNotesPerVideo(): int
    {
        return $this->isPremium() 
            ? config('limits.premium.notes_per_video') 
            : config('limits.free.notes_per_video');
    }

    /**
     * Get the maximum number of tags allowed
     */
    public function maxTags(): int
    {
        return $this->isPremium() 
            ? config('limits.premium.tags') 
            : config('limits.free.tags');
    }

    /**
     * Check if user can export PDF
     */
    public function canExportPdf(): bool
    {
        return $this->isPremium();
    }

    /**
     * Check if user can add more videos
     */
    public function canAddVideo(): bool
    {
        return $this->isPremium() || $this->videos()->count() < $this->maxVideos();
    }

    /**
     * Get remaining videos count
     */
    public function remainingVideos(): int
    {
        if ($this->isPremium()) return PHP_INT_MAX;
        return max(0, $this->maxVideos() - $this->videos()->count());
    }

    /**
     * Check if user can add more notes to a video
     */
    public function canAddNoteToVideo($videoId): bool
    {
        if ($this->isPremium()) return true;
        return $this->notes()->where('video_id', $videoId)->count() < $this->maxNotesPerVideo();
    }

    /**
     * Get remaining notes count for a video
     */
    public function remainingNotesForVideo($videoId): int
    {
        if ($this->isPremium()) return PHP_INT_MAX;
        return max(0, $this->maxNotesPerVideo() - $this->notes()->where('video_id', $videoId)->count());
    }

    /**
     * Check if user can create more tags
     */
    public function canCreateTag(): bool
    {
        return $this->isPremium() || $this->tags()->count() < $this->maxTags();
    }

    /**
     * Get remaining tags count
     */
    public function remainingTags(): int
    {
        if ($this->isPremium()) return PHP_INT_MAX;
        return max(0, $this->maxTags() - $this->tags()->count());
    }
}
