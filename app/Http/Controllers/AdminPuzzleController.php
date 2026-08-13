<?php

namespace App\Http\Controllers;

use App\Models\Puzzle;
use Illuminate\Http\Request;

class AdminPuzzleController extends Controller
{
    /**
     * Display a paginated listing of puzzles with filtering.
     */
    public function index(Request $request)
    {
        $query = Puzzle::query()->withCount('comments');

        // Search Filter (Name, FEN, or Description)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('fen', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Visibility Filter
        if ($request->filled('is_public')) {
            $query->where('is_public', $request->boolean('is_public'));
        }

        $puzzles = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.puzzles.index', compact('puzzles'));
    }

    /**
     * Show the form for creating a new puzzle.
     */
    public function create()
    {
        return view('admin.puzzles.create');
    }

    /**
     * Store a newly created puzzle in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'fen'         => 'required|string|max:500',
            'rating'      => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_public'   => 'boolean',
        ]);

        // Generate unique slug using the model helper method
        $validated['slug'] = Puzzle::makeUniqueSlug($validated['name'], $request->input('slug'));
        $validated['is_public'] = $request->has('is_public');
        $validated['likes_count'] = 0;
        $validated['hard_count'] = 0;
        $validated['unsolved_count'] = 0;

        Puzzle::create($validated);

        return redirect()->route('admin.puzzles.index')->with('success', 'Puzzle created successfully.');
    }

    /**
     * Show the form for editing the specified puzzle.
     */
    public function edit(Puzzle $puzzle)
    {
        $puzzle->loadCount('comments');
        return view('admin.puzzles.edit', compact('puzzle'));
    }

    /**
     * Update the specified puzzle in storage.
     */
    public function update(Request $request, Puzzle $puzzle)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'slug'        => 'nullable|string|max:255',
            'fen'         => 'required|string|max:500',
            'rating'      => 'required|integer|min:0',
            'description' => 'nullable|string',
            'is_public'   => 'boolean',
        ]);

        // Re-generate unique slug if name/preferred slug changed
        if ($request->filled('slug') && $request->input('slug') !== $puzzle->slug) {
            $validated['slug'] = Puzzle::makeUniqueSlug($validated['name'], $request->input('slug'));
        } elseif ($validated['name'] !== $puzzle->name && !$request->filled('slug')) {
            $validated['slug'] = Puzzle::makeUniqueSlug($validated['name']);
        }

        $validated['is_public'] = $request->has('is_public');

        $puzzle->update($validated);

        return redirect()->route('admin.puzzles.index')->with('success', 'Puzzle updated successfully.');
    }

    /**
     * Remove the specified puzzle from storage.
     */
    public function destroy(Puzzle $puzzle)
    {
        $puzzle->delete();
        return redirect()->route('admin.puzzles.index')->with('success', 'Puzzle deleted successfully.');
    }
}
