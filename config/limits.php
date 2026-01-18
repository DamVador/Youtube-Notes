<?php

return [
    'free' => [
        'videos' => env('LIMIT_FREE_VIDEOS', 10),
        'notes_per_video' => env('LIMIT_FREE_NOTES_PER_VIDEO', 20),
        'tags' => env('LIMIT_FREE_TAGS', 10),
    ],
    'premium' => [
        'videos' => PHP_INT_MAX,
        'notes_per_video' => PHP_INT_MAX,
        'tags' => PHP_INT_MAX,
    ],
];