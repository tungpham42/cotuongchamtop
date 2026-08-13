<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AdminTournamentController extends Controller
{
    /**
     * Display a paginated listing of tournaments with filtering.
     */
    public function index(Request $request)
    {
        $query = Tournament::query()->with('creator')->withCount(['users', 'rooms']);

        // Search Filter (Name or Description)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tournaments = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.tournaments.index', compact('tournaments'));
    }

    /**
     * Show the form for creating a new tournament.
     */
    public function create()
    {
        return view('admin.tournaments.create');
    }

    /**
     * Store a newly created tournament in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255|unique:tournaments,slug',
            'description' => 'nullable|string',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'start_date'  => 'required|date',
            'status'      => 'required|string|in:draft,upcoming,ongoing,completed,cancelled',
            'max_players' => 'required|integer|min:2',
        ]);

        $validated['user_id'] = auth()->id();

        // Custom slug override if provided, otherwise the model boot method generates it
        if (!empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        // File upload handling
        if ($request->hasFile('cover_photo')) {
            $validated['cover_photo'] = $request->file('cover_photo')->store('tournaments', 'public');
        }

        Tournament::create($validated);

        return redirect()->route('admin.tournaments.index')->with('success', 'Tournament created successfully.');
    }

    /**
     * Show the form for editing the specified tournament.
     */
    public function edit(Tournament $tournament)
    {
        $tournament->loadCount(['users', 'rooms']);
        return view('admin.tournaments.edit', compact('tournament'));
    }

    /**
     * Update the specified tournament in storage.
     */
    public function update(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'required|string|max:255|unique:tournaments,slug,' . $tournament->id,
            'description' => 'nullable|string',
            'cover_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'start_date'  => 'required|date',
            'status'      => 'required|string|in:draft,upcoming,ongoing,completed,cancelled',
            'max_players' => 'required|integer|min:2',
        ]);

        $validated['slug'] = Str::slug($validated['slug']);

        // Photo Upload Handling
        if ($request->hasFile('cover_photo')) {
            if ($tournament->cover_photo) {
                Storage::disk('public')->delete($tournament->cover_photo);
            }
            $validated['cover_photo'] = $request->file('cover_photo')->store('tournaments', 'public');
        }

        $tournament->update($validated);

        return redirect()->route('admin.tournaments.index')->with('success', 'Tournament updated successfully.');
    }

    /**
     * Remove the specified tournament from storage.
     */
    public function destroy(Tournament $tournament)
    {
        if ($tournament->cover_photo) {
            Storage::disk('public')->delete($tournament->cover_photo);
        }

        $tournament->delete();

        return redirect()->route('admin.tournaments.index')->with('success', 'Tournament deleted successfully.');
    }
}
