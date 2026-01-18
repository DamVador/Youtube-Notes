<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = $request->input('search');
        $tagId = $request->input('tag');
        $videoId = $request->input('video');

        // Quick Notes query
        $notesQuery = $user->notes()
            ->with(['video:id,title,thumbnail,youtube_id', 'tags:id,name,color']);

        if ($search) {
            $notesQuery->whereRaw('MATCH(content) AGAINST(? IN BOOLEAN MODE)', [$search . '*']);
        }

        if ($tagId) {
            $notesQuery->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }

        if ($videoId) {
            $notesQuery->where('video_id', $videoId);
        }

        $quickNotes = $notesQuery->latest()->get()->map(function ($note) {
            $note->type = 'quick_note';
            return $note;
        });

        // Documents query
        $documentsQuery = $user->documents()
            ->whereNotNull('content')
            ->where('content', '!=', '')
            ->with(['video:id,title,thumbnail,youtube_id', 'tags:id,name,color']);

        if ($search) {
            $documentsQuery->whereRaw('MATCH(content) AGAINST(? IN BOOLEAN MODE)', [$search . '*']);
        }

        if ($tagId) {
            $documentsQuery->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }

        if ($videoId) {
            $documentsQuery->where('video_id', $videoId);
        }

        $documents = $documentsQuery->latest('updated_at')->get()->map(function ($doc) {
            $doc->type = 'document';
            $doc->content_preview = \Illuminate\Support\Str::limit(strip_tags($doc->content), 200);
            return $doc;
        });

        // Merge results
        $allNotes = $quickNotes->concat($documents)
            ->sortByDesc(function ($item) {
                return $item->updated_at ?? $item->created_at;
            })
            ->values();

        // Paginate manually
        $page = $request->input('page', 1);
        $perPage = 20;
        $total = $allNotes->count();
        $paginatedNotes = $allNotes->slice(($page - 1) * $perPage, $perPage)->values();

        // Get filter options
        $tags = $user->tags()->withCount(['notes', 'documents'])->get();
        $videos = $user->videos()->select('id', 'title')->orderBy('title')->get();

        return Inertia::render('Notes/Index', [
            'notes' => [
                'data' => $paginatedNotes,
                'current_page' => (int) $page,
                'last_page' => (int) ceil($total / $perPage),
                'per_page' => $perPage,
                'total' => $total,
            ],
            'tags' => $tags,
            'videos' => $videos,
            'filters' => [
                'search' => $search,
                'tag' => $tagId,
                'video' => $videoId,
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'video_id' => 'required|exists:videos,id',
            'content' => 'required|string',
            'timestamp' => 'nullable|integer|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $video = \App\Models\Video::find($validated['video_id']);
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }

        // Check if user can add more notes to this video
        if (!$request->user()->canAddNoteToVideo($validated['video_id'])) {
            return response()->json([
                'error' => 'limit',
                'message' => 'You have reached the maximum of ' . $request->user()->maxNotesPerVideo() . ' quick notes for this video. Upgrade to Premium for unlimited notes.'
            ], 403);
        }

        $note = $request->user()->notes()->create([
            'video_id' => $validated['video_id'],
            'content' => $validated['content'],
            'timestamp' => $validated['timestamp'] ?? null,
        ]);

        if (!empty($validated['tags'])) {
            $note->tags()->sync($validated['tags']);
        }

        $note->load('tags');

        return response()->json($note);
    }

    public function update(Request $request, Note $note)
    {
        if ($note->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => 'required|string',
            'timestamp' => 'nullable|integer|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $note->update([
            'content' => $validated['content'],
            'timestamp' => $validated['timestamp'] ?? $note->timestamp,
        ]);

        if (isset($validated['tags'])) {
            $note->tags()->sync($validated['tags']);
        }

        $note->load('tags');

        return response()->json($note);
    }

    public function destroy(Request $request, Note $note)
    {
        if ($note->user_id !== $request->user()->id) {
            abort(403);
        }

        $note->delete();

        return response()->json(['success' => true]);
    }
}