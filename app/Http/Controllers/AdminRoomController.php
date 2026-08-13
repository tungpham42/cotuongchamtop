<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminRoomController extends Controller
{
    /**
     * Display a paginated listing of rooms with filtering.
     */
    public function index(Request $request)
    {
        $query = Room::query()->with(['host', 'guest', 'tournament']);

        // Search Filter (Code or Name)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            if ($request->status === 'ongoing') {
                $query->ongoing(); // Uses the scopeOngoing from the model
            } elseif ($request->status === 'finished') {
                $query->whereNotNull('result');
            }
        }

        $rooms = $query->orderBy('modified_at', 'desc')->paginate(10)->withQueryString();

        return view('admin.rooms.index', compact('rooms'));
    }

    /**
     * Show the form for creating a new room.
     */
    public function create()
    {
        return view('admin.rooms.create');
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code'          => 'nullable|string|max:255|unique:rooms,code',
            'name'          => 'nullable|string|max:255',
            'fen'           => 'required|string|max:500',
            'pass'          => 'nullable|string|max:255',
            'host_id'       => 'nullable|integer|exists:users,id',
            'guest_id'      => 'nullable|integer|exists:users,id',
            'red_time'      => 'required|numeric|min:0',
            'black_time'    => 'required|numeric|min:0',
            'active_player' => 'nullable|string|in:red,black,waiting,paused:red,paused:black',
        ]);

        // Auto-generate code if left blank
        if (empty($validated['code'])) {
            $validated['code'] = Str::random(8);
        }

        $validated['last_update'] = now();
        $validated['modified_at'] = now();

        Room::create($validated);

        return redirect()->route('admin.rooms.index')->with('success', 'Room created successfully.');
    }

    /**
     * Show the form for editing the specified room.
     */
    public function edit(Room $room)
    {
        return view('admin.rooms.edit', compact('room'));
    }

    /**
     * Update the specified room in storage.
     */
    public function update(Request $request, Room $room)
    {
        $validated = $request->validate([
            'name'          => 'nullable|string|max:255',
            'fen'           => 'required|string|max:500',
            'pass'          => 'nullable|string|max:255',
            'result'        => 'nullable|string|max:255',
            'host_id'       => 'nullable|integer|exists:users,id',
            'guest_id'      => 'nullable|integer|exists:users,id',
            'red_time'      => 'required|numeric|min:0',
            'black_time'    => 'required|numeric|min:0',
            'active_player' => 'nullable|string',
            'host_score'    => 'nullable|numeric',
            'guest_score'   => 'nullable|numeric',
        ]);

        $validated['modified_at'] = now();

        $room->update($validated);

        return redirect()->route('admin.rooms.index')->with('success', 'Room updated successfully.');
    }

    /**
     * Remove the specified room from storage.
     */
    public function destroy(Room $room)
    {
        $room->delete();
        return redirect()->route('admin.rooms.index')->with('success', 'Room deleted successfully.');
    }
}
