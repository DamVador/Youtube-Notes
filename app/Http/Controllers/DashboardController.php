<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Continue watching - dernière vidéo avec position > 0
        $continueWatching = $user->videos()
            ->whereNotNull('last_watched_at')
            ->latest('last_watched_at')
            ->first();
        
        $stats = [
            'videos_count' => $user->videos()->count(),
            'notes_count' => $user->notes()->count(),
            'tags_count' => $user->tags()->count(),
        ];

        // Recent videos with note counts (quick notes + documents)
        $recentVideos = $user->videos()
            ->withCount('notes')
            ->with(['document' => function ($query) {
                $query->select('id', 'video_id', 'content');
            }])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($video) {
                $video->total_notes_count = $video->notes_count + ($video->document && $video->document->content ? 1 : 0);
                return $video;
            });

        // Recent quick notes
        $recentQuickNotes = $user->notes()
            ->with(['video:id,title,thumbnail,youtube_id', 'tags:id,name,color'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($note) {
                $note->type = 'quick_note';
                return $note;
            });

        // Recent documents with content
        $recentDocuments = $user->documents()
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->with(['video:id,title,thumbnail,youtube_id', 'tags:id,name,color'])
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(function ($doc) {
                $doc->type = 'document';
                // Create a preview of the content (strip HTML)
                $doc->content_preview = \Illuminate\Support\Str::limit(strip_tags($doc->content), 150);
                return $doc;
            });

        // Merge and sort by date
        $recentNotes = $recentQuickNotes->concat($recentDocuments)
            ->sortByDesc(function ($item) {
                return $item->updated_at ?? $item->created_at;
            })
            ->take(5)
            ->values();

        return Inertia::render('Dashboard', [
            'continueWatching' => $continueWatching,
            'stats' => $stats,
            'recentVideos' => $recentVideos,
            'recentNotes' => $recentNotes,
        ]);
    }
}