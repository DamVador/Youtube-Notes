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
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? [
                    ...$request->user()->toArray(),
                    'isPremium' => $request->user()->isPremium(),
                    'canExportPdf' => $request->user()->canExportPdf(),
                    'limits' => [
                        'maxVideos' => $request->user()->maxVideos(),
                        'maxNotesPerVideo' => $request->user()->maxNotesPerVideo(),
                        'maxTags' => $request->user()->maxTags(),
                        'videosCount' => $request->user()->videos()->count(),
                        'tagsCount' => $request->user()->tags()->count(),
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
