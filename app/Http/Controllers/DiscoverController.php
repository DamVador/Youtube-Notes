<?php

namespace App\Http\Controllers;

use App\Models\InterestCategory;
use App\Models\UserInterest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DiscoverController extends Controller
{
    /**
     * Get suggested videos based on user interests
     */
    public function suggestions(Request $request)
    {
        $user = $request->user();
        
        // Get user's interests
        $interests = UserInterest::where('user_id', $user->id)
            ->with('category')
            ->get();

        if ($interests->isEmpty()) {
            return response()->json([
                'videos' => [],
                'message' => 'no_interests',
            ]);
        }

        // Get videos from multiple interests (mix)
        $allVideos = [];
        $videosPerInterest = max(2, ceil(8 / $interests->count())); // Distribute 8 videos across interests
        
        foreach ($interests->shuffle()->take(4) as $interest) { // Max 4 interests to avoid too many API calls
            $searchTerm = $interest->search_term . ' tutorial';
            
            // Cache key unique per interest
            $cacheKey = "discover:{$interest->id}:" . now()->format('Y-m-d-H');
            
            $videos = Cache::remember($cacheKey, 3600, function () use ($searchTerm, $videosPerInterest) {
                return $this->searchYouTube($searchTerm, $videosPerInterest);
            });
            
            // Tag videos with their interest
            foreach ($videos as &$video) {
                $video['interest'] = $interest->search_term;
            }
            
            $allVideos = array_merge($allVideos, $videos);
        }
        
        // Shuffle and limit to 8
        shuffle($allVideos);
        $allVideos = array_slice($allVideos, 0, 8);

        return response()->json([
            'videos' => $allVideos,
            'interests' => $interests->pluck('search_term')->unique()->values(),
        ]);
    }

    /**
     * Search YouTube API
     */
    private function searchYouTube(string $query, int $maxResults = 8): array
    {
        $apiKey = config('services.youtube.api_key');
        
        if (!$apiKey) {
            return [];
        }

        try {
            $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
                'part' => 'snippet',
                'q' => $query,
                'type' => 'video',
                'maxResults' => $maxResults,
                'videoDuration' => 'medium', // 4-20 minutes (educational sweet spot)
                'relevanceLanguage' => 'en',
                'safeSearch' => 'strict',
                'key' => $apiKey,
            ]);

            if ($response->successful()) {
                $items = $response->json('items', []);
                
                return array_map(function ($item) {
                    return [
                        'youtube_id' => $item['id']['videoId'],
                        'title' => $item['snippet']['title'],
                        'channel_name' => $item['snippet']['channelTitle'],
                        'thumbnail' => $item['snippet']['thumbnails']['medium']['url'] ?? $item['snippet']['thumbnails']['default']['url'],
                        'published_at' => $item['snippet']['publishedAt'],
                    ];
                }, $items);
            }
        } catch (\Exception $e) {
            \Log::error('YouTube API error: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Get all interest categories
     */
    public function categories()
    {
        $categories = InterestCategory::orderBy('sort_order')->get();
        
        return response()->json($categories);
    }

    /**
     * Get user's interests
     */
    public function userInterests(Request $request)
    {
        $interests = UserInterest::where('user_id', $request->user()->id)
            ->with('category')
            ->get();

        return response()->json($interests);
    }

    /**
     * Update user's interests
     */
    public function updateInterests(Request $request)
    {
        $validated = $request->validate([
            'category_ids' => 'array',
            'category_ids.*' => 'exists:interest_categories,id',
            'custom_keywords' => 'array',
            'custom_keywords.*' => 'string|max:100',
        ]);

        $user = $request->user();

        // Remove old interests
        UserInterest::where('user_id', $user->id)->delete();

        // Add selected categories
        foreach ($validated['category_ids'] ?? [] as $categoryId) {
            UserInterest::create([
                'user_id' => $user->id,
                'interest_category_id' => $categoryId,
            ]);
        }

        // Add custom keywords
        foreach ($validated['custom_keywords'] ?? [] as $keyword) {
            if (trim($keyword)) {
                UserInterest::create([
                    'user_id' => $user->id,
                    'custom_keyword' => trim($keyword),
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Refresh suggestions (clear cache and get new ones)
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        // Clear user's discover cache
        $interests = UserInterest::where('user_id', $user->id)->get();
        foreach ($interests as $interest) {
            $pattern = "discover:{$user->id}:{$interest->id}:*";
            // Simple cache forget for current hour
            Cache::forget("discover:{$user->id}:{$interest->id}:" . now()->format('Y-m-d-H'));
        }

        return $this->suggestions($request);
    }
}