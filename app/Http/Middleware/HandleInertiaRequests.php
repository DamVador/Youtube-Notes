<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
    
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'isPremium' => $user->isPremium(),
                    'canExportPdf' => $user->canExportPdf(),
                    'limits' => [
                        'maxVideos' => $user->maxVideos(),
                        'maxNotesPerVideo' => $user->maxNotesPerVideo(),
                        'maxTags' => $user->maxTags(),
                        'videosCount' => $user->videos()->count(),
                        'tagsCount' => $user->tags()->count(),
                        'remainingVideos' => $user->remainingVideos(),
                        'remainingTags' => $user->remainingTags(),
                        'canAddVideo' => $user->canAddVideo(),
                        'canCreateTag' => $user->canCreateTag(),
                    ],
                ] : null,
            ],
            'flash' => [
                'error' => fn () => $request->session()->get('error'),
                'success' => fn () => $request->session()->get('success'),
            ],
        ];
    }
}
