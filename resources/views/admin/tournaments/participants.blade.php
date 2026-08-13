@extends('layouts.admin')

@section('title', 'Manage Participants')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <div class="flex justify-between items-center mb-6">
        <div>
            <h2 class="text-xl font-semibold text-gray-800">Manage Participants</h2>
            <p class="text-sm text-gray-500">Tournament: <strong>{{ $tournament->name }}</strong> ({{ $tournament->users_count }} / {{ $tournament->max_players }} Players)</p>
        </div>
        <a href="{{ route('admin.tournaments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back to Tournaments
        </a>
    </div>

    <!-- Add Participant Form -->
    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 mb-6">
        <h3 class="text-md font-semibold text-gray-700 mb-4">Add Player</h3>
        <form action="{{ route('admin.tournaments.participants.store', $tournament) }}" method="POST" class="flex items-end gap-4">
            @csrf
            <div class="flex-grow">
                <label class="block text-sm font-medium text-gray-700 mb-1">Select User</label>
                <select name="user_id" required class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="" disabled selected>-- Select a user to add --</option>
                    @foreach($availableUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition disabled:opacity-50" {{ $tournament->users_count >= $tournament->max_players ? 'disabled' : '' }}>
                Add Player
            </button>
        </form>
    </div>

    <!-- Participant List Table -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="p-4">Player Name</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Joined Date</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y text-sm text-gray-700">
                    @forelse ($participants as $participant)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="p-4 font-medium text-gray-900">{{ $participant->name }}</td>
                            <td class="p-4 text-gray-600">{{ $participant->email }}</td>
                            <td class="p-4 text-xs text-gray-600">
                                {{ \Carbon\Carbon::parse($participant->pivot->created_at)->format('M d, Y') }}
                            </td>
                            <td class="p-4 text-right">
                                <form action="{{ route('admin.tournaments.participants.destroy', [$tournament, $participant->id]) }}" method="POST" onsubmit="return confirm('Remove this player from the tournament?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition" title="Remove Player">
                                        <i class="fa-solid fa-user-xmark"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-gray-400">No participants enrolled yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">
            {{ $participants->links() }}
        </div>
    </div>
</div>
@endsection
