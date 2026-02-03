<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserInterest extends Model
{
    protected $fillable = [
        'user_id',
        'interest_category_id',
        'custom_keyword',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(InterestCategory::class, 'interest_category_id');
    }

    /**
     * Get the search term for YouTube API
     */
    public function getSearchTermAttribute(): string
    {
        if ($this->custom_keyword) {
            return $this->custom_keyword;
        }
        
        return $this->category?->name ?? '';
    }
}