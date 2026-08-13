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

            <!-- Custom Searchable Select Wrapper -->
            <div class="flex-grow relative" id="searchable-select-wrapper">
                <label class="block text-sm font-medium text-gray-700 mb-1">Search and Select User</label>

                <!-- Visible Search Input -->
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-400">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </div>
                    <input type="text"
                           id="user-search-input"
                           placeholder="Type name or email to search..."
                           class="w-full pl-10 pr-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                           autocomplete="off"
                           {{ $tournament->users_count >= $tournament->max_players ? 'disabled' : '' }}>
                </div>

                <!-- Hidden Input to store the actual User ID for the form submission -->
                <input type="hidden" name="user_id" id="selected-user-id" required>

                <!-- Custom Dropdown List -->
                <ul id="user-options-list" class="absolute z-10 w-full bg-white border border-gray-200 rounded-lg shadow-xl mt-1 hidden max-h-60 overflow-y-auto">
                    @foreach($availableUsers as $user)
                        <li class="user-option px-4 py-3 text-sm text-gray-700 cursor-pointer hover:bg-indigo-50 hover:text-indigo-700 border-b last:border-b-0"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }} ({{ $user->email }})"
                            data-search="{{ strtolower($user->name . ' ' . $user->email) }}">
                            <div class="font-medium">{{ $user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $user->email }}</div>
                        </li>
                    @endforeach
                    <li id="no-results-msg" class="px-4 py-3 text-sm text-gray-500 hidden text-center italic">
                        No users found matching your search.
                    </li>
                </ul>
            </div>

            <button type="submit" id="submit-btn" class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition disabled:opacity-50" {{ $tournament->users_count >= $tournament->max_players ? 'disabled' : '' }}>
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

<!-- Searchable Select Script -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('user-search-input');
        const hiddenInput = document.getElementById('selected-user-id');
        const optionsList = document.getElementById('user-options-list');
        const options = optionsList.querySelectorAll('.user-option');
        const noResults = document.getElementById('no-results-msg');
        const wrapper = document.getElementById('searchable-select-wrapper');

        if (!searchInput) return;

        // Show the dropdown when the input is focused
        searchInput.addEventListener('focus', () => {
            optionsList.classList.remove('hidden');
        });

        // Filter the list dynamically as the user types
        searchInput.addEventListener('input', function(e) {
            const filterText = e.target.value.toLowerCase();
            let hasVisibleOptions = false;

            // Clear the hidden ID if the user modifies the search box (forces them to re-select)
            hiddenInput.value = '';

            options.forEach(option => {
                const searchableString = option.getAttribute('data-search');

                if (searchableString.includes(filterText)) {
                    option.classList.remove('hidden');
                    hasVisibleOptions = true;
                } else {
                    option.classList.add('hidden');
                }
            });

            // Toggle "No results" message
            if (hasVisibleOptions) {
                noResults.classList.add('hidden');
            } else {
                noResults.classList.remove('hidden');
            }
        });

        // Handle user selection from the dropdown list
        options.forEach(option => {
            option.addEventListener('click', function() {
                // Set the visible input to the user's name
                searchInput.value = this.getAttribute('data-name');

                // Set the hidden input to the user's database ID
                hiddenInput.value = this.getAttribute('data-id');

                // Hide the dropdown
                optionsList.classList.add('hidden');
            });
        });

        // Close the dropdown if the user clicks outside of the component
        document.addEventListener('click', function(e) {
            if (!wrapper.contains(e.target)) {
                optionsList.classList.add('hidden');
            }
        });
    });
</script>
@endsection
