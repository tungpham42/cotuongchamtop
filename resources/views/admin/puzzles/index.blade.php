@extends('layouts.admin')

@section('title', 'Puzzle Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('admin.puzzles.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name, FEN, description..." class="px-4 py-2 border rounded-lg text-sm w-64 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <select name="is_public" class="px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Statuses</option>
                <option value="1" {{ request('is_public') === '1' ? 'selected' : '' }}>Public</option>
                <option value="0" {{ request('is_public') === '0' ? 'selected' : '' }}>Draft / Hidden</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700 transition">Filter</button>
        </form>

        <a href="{{ route('admin.puzzles.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-plus"></i> Add Puzzle
        </a>
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="p-4">Puzzle</th>
                    <th class="p-4">FEN String</th>
                    <th class="p-4">Rating</th>
                    <th class="p-4">Stats</th>
                    <th class="p-4">Status</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm text-gray-700">
                @forelse ($puzzles as $puzzle)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4">
                            <p class="font-medium text-gray-900">{{ $puzzle->name }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $puzzle->slug }}</p>
                        </td>
                        <td class="p-4 max-w-xs">
                            <span class="block truncate font-mono text-xs text-gray-600 bg-gray-100 p-1.5 rounded border border-gray-200" title="{{ $puzzle->fen }}">
                                {{ $puzzle->fen }}
                            </span>
                        </td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 text-xs font-semibold text-amber-800 bg-amber-100 rounded-full">
                                <i class="fa-solid fa-star text-amber-500 mr-1"></i>{{ $puzzle->rating }}
                            </span>
                        </td>
                        <td class="p-4 text-xs text-gray-500 space-y-1">
                            <div><i class="fa-solid fa-thumbs-up text-indigo-500 w-4"></i> {{ number_format($puzzle->likes_count) }}</div>
                            <div><i class="fa-solid fa-comments text-emerald-500 w-4"></i> {{ number_format($puzzle->comments_count) }}</div>
                        </td>
                        <td class="p-4">
                            @if ($puzzle->is_public)
                                <span class="px-2 py-1 text-xs font-semibold text-emerald-700 bg-emerald-100 rounded-full">Public</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-100 rounded-full">Hidden</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.puzzles.edit', $puzzle) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition"><i class="fa-solid fa-pen-to-square"></i></a>
                                <form action="{{ route('admin.puzzles.destroy', $puzzle) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this puzzle?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">No puzzles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $puzzles->links() }}
    </div>
</div>
@endsection
