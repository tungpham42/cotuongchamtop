@extends('layouts.admin')

@section('title', 'Edit Tournament')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Overview Header Stats -->
    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-100 text-center shadow-sm relative">
            <p class="text-xs text-gray-400 font-semibold uppercase">Registered Players</p>
            <p class="text-xl font-bold text-indigo-600 mt-1"><i class="fa-solid fa-users mr-1 text-sm"></i>{{ $tournament->users_count }} / {{ $tournament->max_players }}</p>
            <!-- Added Link Below -->
            <a href="{{ route('admin.tournaments.participants.index', $tournament) }}" class="mt-2 inline-block text-xs text-indigo-600 hover:text-indigo-800 hover:underline">
                Manage Participants &rarr;
            </a>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 text-center shadow-sm">
            <p class="text-xs text-gray-400 font-semibold uppercase">Total Rooms</p>
            <p class="text-xl font-bold text-emerald-600 mt-1"><i class="fa-solid fa-gamepad mr-1 text-sm"></i>{{ $tournament->rooms_count }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 text-center shadow-sm">
            <p class="text-xs text-gray-400 font-semibold uppercase">Created By</p>
            <p class="text-sm font-semibold text-gray-800 mt-2 truncate">{{ $tournament->creator->name ?? 'System Admin' }}</p>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-800">Edit Tournament: {{ $tournament->name }}</h2>
            <a href="{{ route('admin.tournaments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
        </div>

        <form action="{{ route('admin.tournaments.update', $tournament) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tournament Name</label>
                    <input type="text" name="name" value="{{ old('name', $tournament->name) }}" required class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $tournament->slug) }}" required class="w-full px-4 py-2 border rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('slug') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Max Players</label>
                        <input type="number" name="max_players" value="{{ old('max_players', $tournament->max_players) }}" min="2" required class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('max_players') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Date & Time</label>
                        <input type="datetime-local" name="start_date" value="{{ old('start_date', \Carbon\Carbon::parse($tournament->start_date)->format('Y-m-d\TH:i')) }}" required class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('start_date') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="draft" {{ old('status', $tournament->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="upcoming" {{ old('status', $tournament->status) === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="ongoing" {{ old('status', $tournament->status) === 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                            <option value="completed" {{ old('status', $tournament->status) === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ old('status', $tournament->status) === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                        @error('status') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cover Photo</label>
                    @if ($tournament->cover_photo)
                        <div class="mb-2 flex items-center gap-3">
                            <img src="{{ asset('storage/' . $tournament->cover_photo) }}" class="w-16 h-16 rounded-lg object-cover border">
                            <span class="text-xs text-gray-400">Current Cover Photo</span>
                        </div>
                    @endif
                    <input type="file" name="cover_photo" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                    @error('cover_photo') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="4" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $tournament->description) }}</textarea>
                    @error('description') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('admin.tournaments.index') }}" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Update Tournament</button>
            </div>
        </form>
    </div>
</div>
@endsection
