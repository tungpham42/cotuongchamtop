@extends('layouts.admin')

@section('title', 'Tournament Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('admin.tournaments.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tournaments..." class="px-4 py-2 border rounded-lg text-sm w-64 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <select name="status" class="px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700 transition">Filter</button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="p-4">Tournament</th>
                    <th class="p-4">Start Date</th>
                    <th class="p-4">Participants</th>
                    <th class="p-4">Rooms</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm text-gray-700">
                @forelse ($tournaments as $tournament)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4 flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg bg-gray-100 overflow-hidden flex-shrink-0 border">
                                @if ($tournament->cover_photo)
                                    <img src="{{ asset('storage/' . $tournament->cover_photo) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-gray-400">
                                        <i class="fa-solid fa-trophy"></i>
                                    </div>
                                @endif
                            </div>
                            <div>
                                <p class="font-medium text-gray-900">{{ $tournament->name }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $tournament->slug }}</p>
                            </div>
                        </td>
                        <td class="p-4 text-xs text-gray-600">
                            {{ \Carbon\Carbon::parse($tournament->start_date)->format('M d, Y H:i') }}
                        </td>
                        <td class="p-4 font-medium text-xs">
                            <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded-lg">
                                <i class="fa-solid fa-users mr-1"></i> {{ $tournament->users_count }} / {{ $tournament->max_players }}
                            </span>
                        </td>
                        <td class="p-4 font-medium text-xs text-gray-600">
                            <i class="fa-solid fa-gamepad mr-1 text-gray-400"></i> {{ $tournament->rooms_count }}
                        </td>
                        <td class="p-4">
                            @switch($tournament->status)
                                @case('upcoming')
                                    <span class="px-2.5 py-1 text-xs font-semibold text-blue-700 bg-blue-100 rounded-full">Upcoming</span>
                                    @break
                                @case('ongoing')
                                    <span class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100 rounded-full">Ongoing</span>
                                    @break
                                @case('completed')
                                    <span class="px-2.5 py-1 text-xs font-semibold text-gray-700 bg-gray-200 rounded-full">Completed</span>
                                    @break
                                @case('cancelled')
                                    <span class="px-2.5 py-1 text-xs font-semibold text-rose-700 bg-rose-100 rounded-full">Cancelled</span>
                                    @break
                                @default
                                    <span class="px-2.5 py-1 text-xs font-semibold text-amber-700 bg-amber-100 rounded-full">Draft</span>
                            @endswitch
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <!-- New Participants Link -->
                                <a href="{{ route('admin.tournaments.participants.index', $tournament) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Manage Participants">
                                    <i class="fa-solid fa-users-gear"></i>
                                </a>

                                <a href="{{ route('admin.tournaments.edit', $tournament) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition"><i class="fa-solid fa-pen-to-square"></i></a>
                                <form action="{{ route('admin.tournaments.destroy', $tournament) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this tournament?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">No tournaments found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $tournaments->links() }}
    </div>
</div>
@endsection
