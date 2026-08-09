<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Inertia\Inertia;

class VideoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $videos = $user
            ->videos()
            ->withCount('notes')
            ->with(['document' => function ($query) {
                $query->select('id', 'video_id', 'content');
            }])
            ->latest()
            ->paginate(12)
            ->through(function ($video) {
                $video->total_notes_count = $video->notes_count + ($video->document && $video->document->content ? 1 : 0);
                return $video;
            });

        // Most recently watched video, for the "Continue watching" banner.
        $continueWatching = $user->videos()
            ->whereNotNull('last_watched_at')
            ->latest('last_watched_at')
            ->first();

        $stats = [
            'videos_count' => $user->videos()->count(),
            'notes_count' => $user->notes()->count(),
            'documents_count' => $user->documents()->whereNotNull('content')->where('content', '!=', '')->count(),
            'tags_count' => $user->tags()->count(),
        ];

        return Inertia::render('Videos/Index', [
            'videos' => $videos,
            'continueWatching' => $continueWatching,
            'stats' => $stats,
            'showOnboarding' => $user->onboarding_dismissed_at === null,
        ]);
    }

    /**
     * Resolve a YouTube URL (or bare video id) to its public metadata.
     *
     * Uses the free, key-less oEmbed endpoint instead of the YouTube Data API,
     * so it has no quota and cannot be revoked. Always returns usable metadata:
     * if oEmbed is unavailable it falls back to a deterministic thumbnail.
     */
    public function lookup(Request $request)
    {
        $videoId = $this->extractYouTubeId($request->input('url') ?? $request->input('q'));

        if (!$videoId) {
            return response()->json([
                'error' => 'invalid_url',
                'message' => "That doesn't look like a YouTube URL. Paste a link like https://youtube.com/watch?v=…",
            ], 422);
        }

        return response()->json($this->fetchMetadata($videoId));
    }

    public function store(Request $request)
    {
        // Check if user can add more videos
        if (!$request->user()->canAddVideo()) {
            return response()->json([
                'error' => 'limit',
                'message' => 'You have reached the maximum of ' . $request->user()->maxVideos() . ' videos. Upgrade to Premium for unlimited videos.'
            ], 403);
        }

        $validated = $request->validate([
            'youtube_id' => 'required|string',
            'title' => 'nullable|string|max:255',
            'thumbnail' => 'nullable|url',
            'channel_name' => 'nullable|string|max:255',
        ]);

        // Backfill anything the client didn't send so a video is always usable.
        $validated['title'] = $validated['title'] ?? 'YouTube Video';
        $validated['thumbnail'] = $validated['thumbnail'] ?? "https://img.youtube.com/vi/{$validated['youtube_id']}/mqdefault.jpg";
        $validated['channel_name'] = $validated['channel_name'] ?? 'Unknown';

        $video = $request->user()->videos()->updateOrCreate(
            ['youtube_id' => $validated['youtube_id']],
            $validated
        );

        return response()->json($video);
    }

    /**
     * Extract an 11-character YouTube video id from any common URL form
     * (watch, youtu.be, embed, shorts, live) or a bare id.
     */
    private function extractYouTubeId(?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
            return $input;
        }

        $patterns = [
            '/youtube\.com\/watch\?(?:.*&)?v=([a-zA-Z0-9_-]{11})/',
            '/youtu\.be\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/',
            '/youtube\.com\/live\/([a-zA-Z0-9_-]{11})/',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Fetch title / channel / thumbnail for a video id via YouTube's public
     * oEmbed endpoint, falling back to deterministic values on any failure.
     */
    private function fetchMetadata(string $videoId): array
    {
        $fallback = [
            'youtube_id' => $videoId,
            'title' => 'YouTube Video',
            'channel_name' => 'Unknown',
            'thumbnail' => "https://img.youtube.com/vi/{$videoId}/mqdefault.jpg",
        ];

        try {
            $response = Http::timeout(5)->get('https://www.youtube.com/oembed', [
                'url' => "https://www.youtube.com/watch?v={$videoId}",
                'format' => 'json',
            ]);

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'youtube_id' => $videoId,
                    'title' => html_entity_decode($data['title'] ?? $fallback['title']),
                    'channel_name' => $data['author_name'] ?? $fallback['channel_name'],
                    'thumbnail' => $data['thumbnail_url'] ?? $fallback['thumbnail'],
                ];
            }
        } catch (\Exception $e) {
            \Log::warning('YouTube oEmbed lookup failed: ' . $e->getMessage());
        }

        return $fallback;
    }

    public function show(Request $request, Video $video)
    {
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }
        
        $video->update(['last_watched_at' => now()]);

        $video->load(['notes' => function ($query) {
            $query->with('tags')->orderBy('timestamp');
        }]);

        $user = $request->user();

        return Inertia::render('Videos/Show', [
            'video' => $video,
            'onboarding' => [
                'show' => $user->onboarding_dismissed_at === null,
                'hasNote' => $user->notes()->exists()
                    || $user->documents()->whereNotNull('content')->where('content', '!=', '')->exists(),
                'hasTag' => $user->tags()->exists(),
            ],
        ]);
    }

    public function destroy(Request $request, Video $video)
    {
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }

        $video->notes()->delete();
        
        $video->document()->delete();
        
        $video->delete();

        return redirect()->route('videos.index');
    }

    public function updatePosition(Request $request, Video $video)
    {
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }

        $request->validate([
            'position' => 'required|integer|min:0',
        ]);

        $video->update([
            'last_position' => $request->position,
            'last_watched_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
