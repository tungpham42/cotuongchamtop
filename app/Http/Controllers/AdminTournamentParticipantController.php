<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\User;
use Illuminate\Http\Request;

class AdminTournamentParticipantController extends Controller
{
    /**
     * Display a listing of the tournament participants.
     */
    public function index(Tournament $tournament)
    {
        $tournament->loadCount('users');
        $participants = $tournament->users()->paginate(15);

        // Fetch users who are not yet in this tournament for the "Add" dropdown
        $availableUsers = User::whereNotIn('id', $tournament->users()->pluck('users.id'))->get();

        return view('admin.tournaments.participants', compact('tournament', 'participants', 'availableUsers'));
    }

    /**
     * Store (attach) a new participant to the tournament.
     */
    public function store(Request $request, Tournament $tournament)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        // Check if tournament is full
        if ($tournament->users()->count() >= $tournament->max_players) {
            return redirect()->back()->with('error', 'Tournament has reached its maximum player capacity.');
        }

        // Attach user without creating duplicates
        $tournament->users()->syncWithoutDetaching([$request->user_id]);

        return redirect()->back()->with('success', 'Participant added successfully.');
    }

    /**
     * Remove (detach) a participant from the tournament.
     */
    public function destroy(Tournament $tournament, $userId)
    {
        $tournament->users()->detach($userId);

        return redirect()->back()->with('success', 'Participant removed successfully.');
    }
}
