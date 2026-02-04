<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\InterestCategory;
use App\Models\UserInterest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Laravel\Cashier\Subscription;
use Tests\TestCase;

class DiscoverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        InterestCategory::create([
            'name' => 'Programming',
            'slug' => 'programming',
            'icon' => '💻',
            'color' => '#3B82F6',
            'sort_order' => 0,
        ]);
        
        InterestCategory::create([
            'name' => 'Design',
            'slug' => 'design',
            'icon' => '🎨',
            'color' => '#F59E0B',
            'sort_order' => 1,
        ]);
    }

    private function createPremiumUser(): User
    {
        $user = User::factory()->create();
        
        // Créer une subscription active
        $user->subscriptions()->create([
            'type' => 'default',
            'stripe_id' => 'sub_test_' . uniqid(),
            'stripe_status' => 'active',
            'stripe_price' => 'price_test',
        ]);
        
        return $user;
    }

    private function createFreeUser(): User
    {
        return User::factory()->create();
    }

    public function test_guest_cannot_access_discover(): void
    {
        $response = $this->getJson(route('discover.suggestions'));
        
        $response->assertStatus(401);
    }

    public function test_user_can_get_categories(): void
    {
        $user = $this->createFreeUser();
        
        $response = $this->actingAs($user)->getJson(route('discover.categories'));
        
        $response->assertStatus(200);
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['name' => 'Programming']);
        $response->assertJsonFragment(['name' => 'Design']);
    }

    public function test_user_without_interests_gets_no_interests_message(): void
    {
        $user = $this->createFreeUser();
        
        $response = $this->actingAs($user)->getJson(route('discover.suggestions'));
        
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'no_interests',
            'videos' => [],
        ]);
    }

    public function test_user_can_save_interests(): void
    {
        $user = $this->createFreeUser();
        $category = InterestCategory::first();
        
        $response = $this->actingAs($user)->postJson(route('discover.interests.update'), [
            'category_ids' => [$category->id],
            'custom_keywords' => ['Vue.js', 'Laravel'],
        ]);
        
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        
        $this->assertDatabaseHas('user_interests', [
            'user_id' => $user->id,
            'interest_category_id' => $category->id,
        ]);
        
        $this->assertDatabaseHas('user_interests', [
            'user_id' => $user->id,
            'custom_keyword' => 'Vue.js',
        ]);
        
        $this->assertDatabaseHas('user_interests', [
            'user_id' => $user->id,
            'custom_keyword' => 'Laravel',
        ]);
    }

    public function test_user_can_get_their_interests(): void
    {
        $user = $this->createFreeUser();
        $category = InterestCategory::first();
        
        UserInterest::create([
            'user_id' => $user->id,
            'interest_category_id' => $category->id,
        ]);
        
        UserInterest::create([
            'user_id' => $user->id,
            'custom_keyword' => 'React',
        ]);
        
        $response = $this->actingAs($user)->getJson(route('discover.interests'));
        
        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }

    public function test_saving_interests_replaces_old_ones(): void
    {
        $user = $this->createFreeUser();
        $categories = InterestCategory::all();
        
        UserInterest::create([
            'user_id' => $user->id,
            'interest_category_id' => $categories[0]->id,
        ]);
        
        $response = $this->actingAs($user)->postJson(route('discover.interests.update'), [
            'category_ids' => [$categories[1]->id],
            'custom_keywords' => [],
        ]);
        
        $response->assertStatus(200);
        
        $this->assertDatabaseMissing('user_interests', [
            'user_id' => $user->id,
            'interest_category_id' => $categories[0]->id,
        ]);
        
        $this->assertDatabaseHas('user_interests', [
            'user_id' => $user->id,
            'interest_category_id' => $categories[1]->id,
        ]);
    }

    public function test_free_user_has_limited_refreshes(): void
    {
        $user = $this->createFreeUser();
        $category = InterestCategory::first();
        
        UserInterest::create([
            'user_id' => $user->id,
            'interest_category_id' => $category->id,
        ]);
        
        // Simuler 5 refreshes déjà effectués
        $cacheKey = "discover_refresh:{$user->id}:" . now()->format('Y-m-d');
        Cache::put($cacheKey, 5, now()->endOfDay());
        
        $response = $this->actingAs($user)->postJson(route('discover.refresh'));
        
        $response->assertStatus(429);
        $response->assertJson([
            'error' => 'limit_reached',
            'remaining_refreshes' => 0,
        ]);
    }

    public function test_premium_user_has_unlimited_refreshes(): void
    {
        $user = $this->createPremiumUser();
        $category = InterestCategory::first();
        
        UserInterest::create([
            'user_id' => $user->id,
            'interest_category_id' => $category->id,
        ]);
        
        // Simuler beaucoup de refreshes
        $cacheKey = "discover_refresh:{$user->id}:" . now()->format('Y-m-d');
        Cache::put($cacheKey, 100, now()->endOfDay());
        
        $response = $this->actingAs($user)->postJson(route('discover.refresh'));
        
        // Premium ne devrait pas être bloqué
        $response->assertStatus(200);
    }

    public function test_refresh_increments_counter_for_free_user(): void
    {
        $user = $this->createFreeUser();
        $category = InterestCategory::first();
        
        UserInterest::create([
            'user_id' => $user->id,
            'interest_category_id' => $category->id,
        ]);
        
        $cacheKey = "discover_refresh:{$user->id}:" . now()->format('Y-m-d');
        
        $this->assertEquals(0, Cache::get($cacheKey, 0));
        
        $this->actingAs($user)->postJson(route('discover.refresh'));
        
        $this->assertEquals(1, Cache::get($cacheKey, 0));
    }

    public function test_suggestions_returns_remaining_refreshes(): void
    {
        $user = $this->createFreeUser();
        $category = InterestCategory::first();
        
        UserInterest::create([
            'user_id' => $user->id,
            'interest_category_id' => $category->id,
        ]);
        
        // 2 refreshes déjà faits
        $cacheKey = "discover_refresh:{$user->id}:" . now()->format('Y-m-d');
        Cache::put($cacheKey, 2, now()->endOfDay());
        
        $response = $this->actingAs($user)->getJson(route('discover.suggestions'));
        
        $response->assertStatus(200);
        $response->assertJson([
            'remaining_refreshes' => 3, // 5 - 2
            'is_premium' => false,
        ]);
    }

    public function test_premium_user_has_null_remaining_refreshes(): void
    {
        $user = $this->createPremiumUser();
        $category = InterestCategory::first();
        
        UserInterest::create([
            'user_id' => $user->id,
            'interest_category_id' => $category->id,
        ]);
        
        $response = $this->actingAs($user)->getJson(route('discover.suggestions'));
        
        $response->assertStatus(200);
        $response->assertJson([
            'remaining_refreshes' => null,
            'is_premium' => true,
        ]);
    }
}