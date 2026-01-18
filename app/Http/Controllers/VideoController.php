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
        $videos = $request->user()
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

        return Inertia::render('Videos/Index', [
            'videos' => $videos,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json([]);
        }

        $apiKey = config('services.youtube.api_key');

        $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
            'part' => 'snippet',
            'q' => $query,
            'type' => 'video',
            'maxResults' => 10,
            'key' => $apiKey,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'YouTube API error'], 500);
        }

        $items = $response->json('items') ?? [];

        $results = collect($items)->map(function ($item) {
            // Safely get videoId from nested structure
            $videoId = $item['id']['videoId'] ?? $item['id'] ?? null;
            
            // Skip if no videoId (might be a channel or playlist)
            if (!$videoId || !is_string($videoId)) {
                return null;
            }

            return [
                'youtube_id' => $videoId,
                'title' => html_entity_decode($item['snippet']['title'] ?? 'Untitled'),
                'thumbnail' => $item['snippet']['thumbnails']['medium']['url'] 
                    ?? $item['snippet']['thumbnails']['default']['url'] 
                    ?? null,
                'channel_name' => $item['snippet']['channelTitle'] ?? 'Unknown',
            ];
        })->filter()->values();

        return response()->json($results);
    }

    public function store(Request $request)
    {
        // Check if user can add more videos
        if (!$request->user()->canAddVideo()) {
            return back()->with('error', 'You have reached the maximum number of videos for your plan. Upgrade to Premium for unlimited videos.');
        }

        $validated = $request->validate([
            'youtube_id' => 'required|string',
            'title' => 'required|string|max:255',
            'thumbnail' => 'nullable|url',
            'channel_name' => 'nullable|string|max:255',
        ]);

        $video = $request->user()->videos()->updateOrCreate(
            ['youtube_id' => $validated['youtube_id']],
            $validated
        );

        return response()->json($video);
    }

    public function show(Request $request, Video $video)
    {
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }

        $video->load(['notes' => function ($query) {
            $query->with('tags')->orderBy('timestamp');
        }]);

        return Inertia::render('Videos/Show', [
            'video' => $video,
        ]);
    }

    public function destroy(Request $request, Video $video)
    {
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }

        $video->delete();

        return redirect()->route('videos.index');
    }
}
