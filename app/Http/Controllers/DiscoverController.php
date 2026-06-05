<?php

namespace App\Http\Controllers;

use App\Models\InterestCategory;
use App\Models\UserInterest;
use Illuminate\Http\Request;
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
     *
     * Suggestions previously came from the YouTube Data API, which has been
     * removed. They stay empty until a non-API source (e.g. channel RSS feeds)
     * is wired up; user interests are preserved so that work can build on them.
     */
    private function generateSuggestions($user, $interests): array
    {
        return [
            'videos' => [],
            'interests' => $interests->pluck('search_term')->unique()->values(),
        ];
    }
}