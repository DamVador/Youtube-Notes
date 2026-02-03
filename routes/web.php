<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NoteController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TagController;
use App\Http\Controllers\VideoController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WebhookController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\AdminPostController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\DiscoverController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('welcome');

// Routes protégées (authentification requise)
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Videos
    Route::get('/videos', [VideoController::class, 'index'])->name('videos.index');
    Route::get('/videos/search', [VideoController::class, 'search'])->name('videos.search');
    Route::post('/videos', [VideoController::class, 'store'])->name('videos.store');
    Route::get('/videos/{video}', [VideoController::class, 'show'])->name('videos.show');
    Route::delete('/videos/{video}', [VideoController::class, 'destroy'])->name('videos.destroy');
    Route::patch('/videos/{video}/position', [VideoController::class, 'updatePosition'])->name('videos.updatePosition');

    // Notes
    Route::get('/notes', [NoteController::class, 'index'])->name('notes.index');
    Route::post('/notes', [NoteController::class, 'store'])->name('notes.store');
    Route::put('/notes/{note}', [NoteController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{note}', [NoteController::class, 'destroy'])->name('notes.destroy');

    // Tags
    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::post('/tags', [TagController::class, 'store'])->name('tags.store');
    Route::put('/tags/{tag}', [TagController::class, 'update'])->name('tags.update');
    Route::delete('/tags/{tag}', [TagController::class, 'destroy'])->name('tags.destroy');

    // Documents
    Route::get('/videos/{video}/document', [DocumentController::class, 'show'])->name('documents.show');
    Route::post('/videos/{video}/document', [DocumentController::class, 'store'])->name('documents.store');

    // Subscription
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/subscription/success', [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/billing-portal', [SubscriptionController::class, 'billingPortal'])->name('subscription.billing');
});

// Legal
Route::get('/terms', [LegalController::class, 'terms'])->name('legal.terms');
Route::get('/privacy', [LegalController::class, 'privacy'])->name('legal.privacy');

// Google OAuth routes
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('auth.google');
Route::get('/auth/google/callback', [GoogleController::class, 'callback']);

Route::get('/pricing', [SubscriptionController::class, 'pricing'])->name('subscription.pricing');

Route::post('/stripe/webhook', [WebhookController::class, 'handleWebhook'])->name('cashier.webhook');

Route::get('/features', function () {
    return Inertia::render('Features', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('features');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::get('/sitemap.xml', [SitemapController::class, 'index']);

Route::post('/preprod-auth', function (\Illuminate\Http\Request $request) {
    if ($request->input('preprod_password') === config('app.preprod_password')) {
        $request->session()->put('preprod_authenticated', true);
        return redirect($request->input('redirect_to', '/'));
    }
    return back();
})->name('preprod.auth')->withoutMiddleware([\App\Http\Middleware\PreprodProtection::class]);

// Routes profil (Breeze)
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Discover routes (authenticated)
Route::middleware(['auth', 'verified'])->prefix('discover')->name('discover.')->group(function () {
    Route::get('/suggestions', [DiscoverController::class, 'suggestions'])->name('suggestions');
    Route::get('/categories', [DiscoverController::class, 'categories'])->name('categories');
    Route::get('/interests', [DiscoverController::class, 'userInterests'])->name('interests');
    Route::post('/interests', [DiscoverController::class, 'updateInterests'])->name('interests.update');
    Route::post('/refresh', [DiscoverController::class, 'refresh'])->name('refresh');
});

// Admin routes
Route::middleware(['auth', 'verified', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}', [AdminController::class, 'showUser'])->name('users.show');
    Route::post('/users/{user}/toggle-premium', [AdminController::class, 'togglePremium'])->name('users.toggle-premium');
    Route::post('/users/{user}/toggle-admin', [AdminController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('users.delete');
    Route::resource('posts', AdminPostController::class);
});

require __DIR__.'/auth.php';