<?php

namespace App\Http\Controllers;

use App\Models\InterestCategory;
use App\Models\UserInterest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class DiscoverController extends Controller
{
    // Limites par type d'utilisateur
    const FREE_DAILY_LIMIT = 5;
    const PREMIUM_DAILY_LIMIT = null;

    /**
     * Get remaining refresh count for user
     */
    private function getRemainingRefreshes($user): ?int
    {
        // Premium = illimité
        if ($user->isPremium()) {
            return null;
        }

        $cacheKey = "discover_refresh:{$user->id}:" . now()->format('Y-m-d');
        $used = Cache::get($cacheKey, 0);
        
        return max(0, self::FREE_DAILY_LIMIT - $used);
    }

    /**
     * Increment refresh count
     */
    private function incrementRefreshCount($user): void
    {
        if ($user->isPremium()) {
            return;
        }

        $cacheKey = "discover_refresh:{$user->id}:" . now()->format('Y-m-d');
        $used = Cache::get($cacheKey, 0);
        Cache::put($cacheKey, $used + 1, now()->endOfDay());
    }

    /**
     * Check if user can refresh
     */
    private function canRefresh($user): bool
    {
        if ($user->isPremium()) {
            return true;
        }

        $remaining = $this->getRemainingRefreshes($user);
        return $remaining > 0;
    }

    /**
     * Get suggested videos (uses cache, doesn't count as refresh)
     */
    public function suggestions(Request $request)
    {
        $user = $request->user();
        
        $interests = UserInterest::where('user_id', $user->id)
            ->with('category')
            ->get();

        if ($interests->isEmpty()) {
            return response()->json([
                'videos' => [],
                'message' => 'no_interests',
                'remaining_refreshes' => $this->getRemainingRefreshes($user),
                'is_premium' => $user->isPremium(),
            ]);
        }

        // Cache des résultats par utilisateur (valide 1h)
        $cacheKey = "discover:user:{$user->id}:" . now()->format('Y-m-d-H');
        
        $cached = Cache::get($cacheKey);
        
        if ($cached) {
            return response()->json([
                'videos' => $cached['videos'],
                'interests' => $cached['interests'],
                'remaining_refreshes' => $this->getRemainingRefreshes($user),
                'is_premium' => $user->isPremium(),
            ]);
        }

        // Pas de cache, on génère (première visite de l'heure)
        $result = $this->generateSuggestions($user, $interests);
        
        Cache::put($cacheKey, $result, 3600);

        return response()->json([
            'videos' => $result['videos'],
            'interests' => $result['interests'],
            'remaining_refreshes' => $this->getRemainingRefreshes($user),
            'is_premium' => $user->isPremium(),
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
                'videoDuration' => 'medium',
                'relevanceLanguage' => 'en',
                'safeSearch' => 'strict',
                'key' => $apiKey,
            ]);

            if ($response->successful()) {
                $items = $response->json('items', []);
                
                return array_map(function ($item) {
                    return [
                        'youtube_id' => $item['id']['videoId'],
                        'title' => html_entity_decode($item['snippet']['title']),
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

        UserInterest::where('user_id', $user->id)->delete();

        foreach ($validated['category_ids'] ?? [] as $categoryId) {
            UserInterest::create([
                'user_id' => $user->id,
                'interest_category_id' => $categoryId,
            ]);
        }

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
     * Refresh suggestions (clears cache, counts as refresh)
     */
    public function refresh(Request $request)
    {
        $user = $request->user();
        
        if (!$this->canRefresh($user)) {
            return response()->json([
                'error' => 'limit_reached',
                'message' => 'Daily refresh limit reached.',
                'remaining_refreshes' => 0,
                'is_premium' => false,
            ], 429);
        }

        // Increment counter
        $this->incrementRefreshCount($user);
        
        // Clear user cache
        $cacheKey = "discover:user:{$user->id}:" . now()->format('Y-m-d-H');
        Cache::forget($cacheKey);

        // Generate new suggestions
        $interests = UserInterest::where('user_id', $user->id)
            ->with('category')
            ->get();
        
        $result = $this->generateSuggestions($user, $interests);
        
        Cache::put($cacheKey, $result, 3600);

        return response()->json([
            'videos' => $result['videos'],
            'interests' => $result['interests'],
            'remaining_refreshes' => $this->getRemainingRefreshes($user),
            'is_premium' => $user->isPremium(),
        ]);
    }

    /**
     * Generate suggestions (internal)
     */
    private function generateSuggestions($user, $interests): array
    {
        $allVideos = [];
        $videosPerInterest = max(2, ceil(8 / $interests->count()));
        
        foreach ($interests->shuffle()->take(4) as $interest) {
            $searchTerm = $interest->search_term . ' tutorial';
            
            $cacheKey = "discover:{$interest->id}:" . now()->format('Y-m-d-H');
            
            $videos = Cache::remember($cacheKey, 3600, function () use ($searchTerm, $videosPerInterest) {
                return $this->searchYouTube($searchTerm, $videosPerInterest);
            });
            
            foreach ($videos as &$video) {
                $video['interest'] = $interest->search_term;
            }
            
            $allVideos = array_merge($allVideos, $videos);
        }
        
        shuffle($allVideos);
        $allVideos = array_slice($allVideos, 0, 8);

        return [
            'videos' => $allVideos,
            'interests' => $interests->pluck('search_term')->unique()->values(),
        ];
    }
}