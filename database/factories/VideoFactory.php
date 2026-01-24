<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'youtube_id' => $this->faker->regexify('[A-Za-z0-9_-]{11}'),
            'title' => $this->faker->sentence(),
            'thumbnail' => $this->faker->imageUrl(320, 180),
            'channel_name' => $this->faker->company(),
            'last_position' => 0,
            'last_watched_at' => null,
        ];
    }
}