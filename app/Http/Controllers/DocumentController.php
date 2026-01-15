<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Video;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function show(Request $request, Video $video)
    {
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }

        $document = $video->document;
        
        if ($document) {
            $document->load('tags');
        }

        return response()->json($document);
    }

    public function store(Request $request, Video $video)
    {
        if ($video->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'content' => 'nullable|string',
            'content_json' => 'nullable|array',
            'tags' => 'nullable|array',
            'tags.*' => 'exists:tags,id',
        ]);

        $document = Document::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'video_id' => $video->id,
            ],
            [
                'content' => $validated['content'] ?? '',
                'content_json' => $validated['content_json'] ?? null,
            ]
        );

        if (isset($validated['tags'])) {
            $document->tags()->sync($validated['tags']);
        }

        $document->load('tags');

        return response()->json($document);
    }
}