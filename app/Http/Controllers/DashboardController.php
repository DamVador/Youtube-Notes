<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $videos = $user->videos()
            ->withCount('notes')
            ->orderBy('updated_at', 'desc')
            ->take(10)
            ->get();

        $recentNotes = $user->notes()
            ->with(['video', 'tags'])
            ->orderBy('updated_at', 'desc')
            ->take(5)
            ->get();

        return Inertia::render('Dashboard', [
            'videos' => $videos,
            'recentNotes' => $recentNotes,
        ]);
    }
}