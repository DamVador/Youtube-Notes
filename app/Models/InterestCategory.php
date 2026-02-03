<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InterestCategory extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'icon',
        'color',
        'sort_order',
    ];

    public function userInterests(): HasMany
    {
        return $this->hasMany(UserInterest::class);
    }
}