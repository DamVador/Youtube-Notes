<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use App\Models\Note;
use App\Models\Tag;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'premium_users' => User::whereHas('subscriptions', function ($q) {
                $q->where('stripe_status', 'active');
            })->count(),
            'total_videos' => Video::count(),
            'total_notes' => Note::count(),
            'total_tags' => Tag::count(),
            'recent_users' => User::latest()->take(5)->get(['id', 'name', 'email', 'created_at']),
        ];

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
        ]);
    }

    public function users(Request $request)
    {
        $search = $request->input('search');

        $users = User::query()
            ->withCount(['videos', 'notes', 'tags'])
            ->with(['subscriptions' => function ($q) {
                $q->where('stripe_status', 'active');
            }])
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(20);

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function showUser(User $user)
    {
        $user->load(['subscriptions', 'videos', 'notes', 'tags']);
        $user->loadCount(['videos', 'notes', 'tags']);

        return Inertia::render('Admin/UserDetail', [
            'user' => $user,
        ]);
    }

    public function togglePremium(User $user)
    {
        $activeSubscription = $user->subscriptions()
            ->where('stripe_status', 'active')
            ->first();

        if ($activeSubscription) {
            // Désactiver TOUS les abonnements actifs
            $user->subscriptions()
                ->where('stripe_status', 'active')
                ->update([
                    'stripe_status' => 'canceled',
                    'ends_at' => now(), // Important pour Cashier
                ]);
            $message = 'Premium désactivé pour ' . $user->name;
        } else {
            // Activer - d'abord s'assurer qu'il n'y a pas de doublons actifs
            $user->subscriptions()
                ->where('stripe_status', 'active')
                ->update(['stripe_status' => 'canceled', 'ends_at' => now()]);
                
            $user->subscriptions()->create([
                'type' => 'premium',
                'stripe_id' => 'sub_admin_manual_' . time(),
                'stripe_status' => 'active',
                'stripe_price' => config('services.stripe.monthly_price_id'),
                'quantity' => 1,
                'ends_at' => null,
            ]);
            $message = 'Premium activé pour ' . $user->name;
        }

        return back()->with('success', $message);
    }

    public function toggleAdmin(User $user)
    {
        $user->update(['is_admin' => !$user->is_admin]);

        $message = $user->is_admin 
            ? $user->name . ' est maintenant admin'
            : $user->name . ' n\'est plus admin';

        return back()->with('success', $message);
    }

    public function deleteUser(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'Impossible de supprimer un admin');
        }

        $user->videos()->delete();
        $user->notes()->delete();
        $user->tags()->delete();
        $user->documents()->delete();
        $user->subscriptions()->delete();
        $user->delete();

        return redirect()->route('admin.users')->with('success', 'Utilisateur supprimé');
    }
}