@extends('layouts.admin')

@section('title', 'Room Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('admin.rooms.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search code or name..." class="px-4 py-2 border rounded-lg text-sm w-64 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <select name="status" class="px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Statuses</option>
                <option value="ongoing" {{ request('status') === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                <option value="finished" {{ request('status') === 'finished' ? 'selected' : '' }}>Finished</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700 transition">Filter</button>
        </form>

        <a href="{{ route('admin.rooms.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i> Create Room
        </a>
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="p-4">Code / Name</th>
                    <th class="p-4">Players</th>
                    <th class="p-4">Active Player</th>
                    <th class="p-4">Result</th>
                    <th class="p-4">Modified</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm text-gray-700">
                @forelse ($rooms as $room)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4">
                            <p class="font-mono font-medium text-gray-900">{{ $room->code }}</p>
                            <p class="text-xs text-gray-500">{{ $room->name ?? 'Unnamed Room' }}</p>
                            @if($room->pass)
                                <i class="fa-solid fa-lock text-xs text-rose-500 mt-1" title="Password Protected"></i>
                            @endif
                        </td>
                        <td class="p-4 text-xs">
                            <div class="text-rose-600 font-medium">Red: {{ $room->host->name ?? ($room->host_session ? 'Anon' : 'Waiting...') }}</div>
                            <div class="text-gray-800 font-medium">Blk: {{ $room->guest->name ?? ($room->guest_session ? 'Anon' : 'Waiting...') }}</div>
                        </td>
                        <td class="p-4">
                            @if ($room->active_player === 'red')
                                <span class="px-2 py-1 text-xs font-semibold text-rose-700 bg-rose-100 rounded-full">Red</span>
                            @elseif ($room->active_player === 'black')
                                <span class="px-2 py-1 text-xs font-semibold text-gray-700 bg-gray-200 rounded-full">Black</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-indigo-700 bg-indigo-100 rounded-full capitalize">{{ $room->active_player ?? 'N/A' }}</span>
                            @endif
                        </td>
                        <td class="p-4">
                            @if ($room->result)
                                <span class="px-2 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100 rounded-full">{{ $room->result }}</span>
                            @else
                                <span class="text-gray-400 text-xs italic">Ongoing</span>
                            @endif
                        </td>
                        <td class="p-4 text-xs text-gray-500">
                            {{ \Carbon\Carbon::parse($room->modified_at)->diffForHumans() }}
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.rooms.edit', $room->code) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition"><i class="fa-solid fa-pen-to-square"></i></a>
                                <form action="{{ route('admin.rooms.destroy', $room->code) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this room?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">No rooms found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $rooms->links() }}
    </div>
</div>
@endsection
