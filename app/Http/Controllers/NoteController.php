<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Document;
use App\Models\Video;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $tagId = $request->input('tag');
        $videoId = $request->input('video');
        $type = $request->input('type', 'all'); // 'all', 'documents', 'quick_notes'

        $allNotes = collect();

        // Récupérer les Documents (éditeur riche)
        if ($type === 'all' || $type === 'documents') {
            $documentsQuery = $request->user()->documents()->with(['video', 'tags']);

            if ($search) {
                $documentsQuery->where(function ($q) use ($search) {
                    $q->where('content', 'like', '%' . $search . '%');
                });
            }

            if ($tagId) {
                $documentsQuery->whereHas('tags', function ($q) use ($tagId) {
                    $q->where('tags.id', $tagId);
                });
            }

            if ($videoId) {
                $documentsQuery->where('video_id', $videoId);
            }

            $documents = $documentsQuery->get()->map(function ($doc) {
                return [
                    'id' => 'doc_' . $doc->id,
                    'original_id' => $doc->id,
                    'type' => 'document',
                    'content' => $doc->content,
                    'content_preview' => $this->stripHtml($doc->content, 200),
                    'timestamp' => null,
                    'video' => $doc->video,
                    'tags' => $doc->tags,
                    'updated_at' => $doc->updated_at,
                    'created_at' => $doc->created_at,
                ];
            });

            $allNotes = $allNotes->concat($documents);
        }

        // Récupérer les Quick Notes
        if ($type === 'all' || $type === 'quick_notes') {
            $notesQuery = $request->user()->notes()->with(['video', 'tags']);

            if ($search) {
                $notesQuery->where('content', 'like', '%' . $search . '%');
            }

            if ($tagId) {
                $notesQuery->whereHas('tags', function ($q) use ($tagId) {
                    $q->where('tags.id', $tagId);
                });
            }

            if ($videoId) {
                $notesQuery->where('video_id', $videoId);
            }

            $notes = $notesQuery->get()->map(function ($note) {
                return [
                    'id' => 'note_' . $note->id,
                    'original_id' => $note->id,
                    'type' => 'quick_note',
                    'content' => $note->content,
                    'content_preview' => $note->content,
                    'timestamp' => $note->timestamp,
                    'video' => $note->video,
                    'tags' => $note->tags,
                    'updated_at' => $note->updated_at,
                    'created_at' => $note->created_at,
                ];
            });

            $allNotes = $allNotes->concat($notes);
        }

        // Trier par date de mise à jour
        $allNotes = $allNotes->sortByDesc('updated_at')->values();

        // Pagination manuelle
        $page = $request->input('page', 1);
        $perPage = 20;
        $total = $allNotes->count();
        $paginatedNotes = $allNotes->slice(($page - 1) * $perPage, $perPage)->values();

        $tags = $request->user()->tags()->withCount('notes')->get();
        $videos = $request->user()->videos()->select('id', 'title')->get();

        return Inertia::render('Notes/Index', [
            'notes' => [
                'data' => $paginatedNotes,
                'current_page' => (int) $page,
                'last_page' => ceil($total / $perPage),
                'per_page' => $perPage,
                'total' => $total,
            ],
            'tags' => $tags,
            'videos' => $videos,
            'filters' => [
                'search' => $search,
                'tag' => $tagId,
                'video' => $videoId,
                'type' => $type,
            ],
        ]);
    }

    private function stripHtml($html, $maxLength = 200)
    {
        $text = strip_tags($html);
        $text = html_entity_decode($text);
        $text = preg_replace('/\s+/', ' ', $text);
        $text = trim($text);
        
        if (strlen($text) > $maxLength) {
            $text = substr($text, 0, $maxLength) . '...';
        }
        
        return $text;
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