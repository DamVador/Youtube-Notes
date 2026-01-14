<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->user()->notes()->with(['video', 'tags']);

        // Recherche par mot-clé
        if ($search = $request->input('search')) {
            $query->whereFullText('content', $search)
                  ->orWhere('content', 'like', '%' . $search . '%');
        }

        // Filtre par tag
        if ($tagId = $request->input('tag')) {
            $query->whereHas('tags', function ($q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }

        // Filtre par vidéo
        if ($videoId = $request->input('video')) {
            $query->where('video_id', $videoId);
        }

        $notes = $query->orderBy('updated_at', 'desc')->paginate(20);

        $tags = $request->user()->tags()->withCount('notes')->get();
        $videos = $request->user()->videos()->select('id', 'title')->get();

        return Inertia::render('Notes/Index', [
            'notes' => $notes,
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

        // Vérifier que la vidéo appartient à l'utilisateur
        $video = Video::findOrFail($validated['video_id']);
        if ($video->user_id !== $request->user()->id) {
            abort(403);
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