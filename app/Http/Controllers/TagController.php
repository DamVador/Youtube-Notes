<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;

class TagController extends Controller
{
    public function index(Request $request)
    {
        $tags = $request->user()
            ->tags()
            ->withCount('notes')
            ->orderBy('name')
            ->get();

        return response()->json($tags);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $tag = $request->user()->tags()->create([
            'name' => $validated['name'],
            'color' => $validated['color'] ?? '#3B82F6',
        ]);

        return response()->json($tag);
    }

    public function update(Request $request, Tag $tag)
    {
        if ($tag->user_id !== $request->user()->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'color' => 'nullable|string|size:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ]);

        $tag->update($validated);

        return response()->json($tag);
    }

    public function destroy(Request $request, Tag $tag)
    {
        if ($tag->user_id !== $request->user()->id) {
            abort(403);
        }

        $tag->delete();

        return response()->json(['success' => true]);
    }
}