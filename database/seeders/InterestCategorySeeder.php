<?php

namespace Database\Seeders;

use App\Models\InterestCategory;
use Illuminate\Database\Seeder;

class InterestCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Programming', 'slug' => 'programming', 'icon' => '💻', 'color' => '#3B82F6'],
            ['name' => 'Web Development', 'slug' => 'web-development', 'icon' => '🌐', 'color' => '#10B981'],
            ['name' => 'Data Science', 'slug' => 'data-science', 'icon' => '📊', 'color' => '#8B5CF6'],
            ['name' => 'Machine Learning', 'slug' => 'machine-learning', 'icon' => '🤖', 'color' => '#EC4899'],
            ['name' => 'Design', 'slug' => 'design', 'icon' => '🎨', 'color' => '#F59E0B'],
            ['name' => 'Business', 'slug' => 'business', 'icon' => '💼', 'color' => '#6366F1'],
            ['name' => 'Marketing', 'slug' => 'marketing', 'icon' => '📢', 'color' => '#EF4444'],
            ['name' => 'Productivity', 'slug' => 'productivity', 'icon' => '⚡', 'color' => '#14B8A6'],
            ['name' => 'Languages', 'slug' => 'languages', 'icon' => '🗣️', 'color' => '#F97316'],
            ['name' => 'Science', 'slug' => 'science', 'icon' => '🔬', 'color' => '#06B6D4'],
            ['name' => 'History', 'slug' => 'history', 'icon' => '📜', 'color' => '#84CC16'],
            ['name' => 'Philosophy', 'slug' => 'philosophy', 'icon' => '🤔', 'color' => '#A855F7'],
            ['name' => 'Music', 'slug' => 'music', 'icon' => '🎵', 'color' => '#EC4899'],
            ['name' => 'Finance', 'slug' => 'finance', 'icon' => '💰', 'color' => '#22C55E'],
            ['name' => 'Health & Fitness', 'slug' => 'health-fitness', 'icon' => '💪', 'color' => '#EF4444'],
            ['name' => 'Cooking', 'slug' => 'cooking', 'icon' => '🍳', 'color' => '#F59E0B'],
        ];

        foreach ($categories as $index => $category) {
            InterestCategory::updateOrCreate(
                ['slug' => $category['slug']],
                array_merge($category, ['sort_order' => $index])
            );
        }
    }
}