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
            ->orderBy('updated_at', 'desc')
            ->paginate(12);

        return Inertia::render('Videos/Index', [
            'videos' => $videos,
        ]);
    }

    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json(['items' => []]);
        }

        $apiKey = config('services.youtube.api_key');

        $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
            'part' => 'snippet',
            'q' => $query,
            'type' => 'video',
            'maxResults' => 12,
            'key' => $apiKey,
        ]);

        if ($response->failed()) {
            return response()->json(['items' => [], 'error' => 'YouTube API error'], 500);
        }

        $items = collect($response->json('items'))->map(function ($item) {
            return [
                'youtube_id' => $item['id']['videoId'],
                'title' => $item['snippet']['title'],
                'thumbnail' => $item['snippet']['thumbnails']['medium']['url'] ?? null,
                'channel_name' => $item['snippet']['channelTitle'],
            ];
        });

        return response()->json(['items' => $items]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'youtube_id' => 'required|string',
            'title' => 'required|string|max:255',
            'thumbnail' => 'nullable|string',
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
        // Vérifier que la vidéo appartient à l'utilisateur
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }

        $video->load(['notes.tags']);

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