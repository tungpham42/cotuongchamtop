@extends('layouts.admin')

@section('title', 'Edit Puzzle')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Engagement Stats Header -->
    <div class="grid grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-xl border border-gray-100 text-center shadow-sm">
            <p class="text-xs text-gray-400 font-semibold uppercase">Likes</p>
            <p class="text-xl font-bold text-indigo-600 mt-1"><i class="fa-solid fa-thumbs-up mr-1 text-sm"></i>{{ number_format($puzzle->likes_count) }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 text-center shadow-sm">
            <p class="text-xs text-gray-400 font-semibold uppercase">Marked Hard</p>
            <p class="text-xl font-bold text-amber-600 mt-1"><i class="fa-solid fa-fire mr-1 text-sm"></i>{{ number_format($puzzle->hard_count) }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 text-center shadow-sm">
            <p class="text-xs text-gray-400 font-semibold uppercase">Unsolved</p>
            <p class="text-xl font-bold text-rose-600 mt-1"><i class="fa-solid fa-xmark mr-1 text-sm"></i>{{ number_format($puzzle->unsolved_count) }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-100 text-center shadow-sm">
            <p class="text-xs text-gray-400 font-semibold uppercase">Comments</p>
            <p class="text-xl font-bold text-emerald-600 mt-1"><i class="fa-solid fa-comments mr-1 text-sm"></i>{{ number_format($puzzle->comments_count) }}</p>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-800">Edit Puzzle: {{ $puzzle->name }}</h2>
            <a href="{{ route('admin.puzzles.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
        </div>

        <form action="{{ route('admin.puzzles.update', $puzzle) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Puzzle Name</label>
                    <input type="text" name="name" value="{{ old('name', $puzzle->name) }}" required class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Slug</label>
                        <input type="text" name="slug" value="{{ old('slug', $puzzle->slug) }}" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('slug') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Rating / Difficulty</label>
                        <input type="number" name="rating" value="{{ old('rating', $puzzle->rating) }}" required class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('rating') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">FEN Notation</label>
                    <textarea name="fen" rows="2" required class="w-full px-4 py-2 border rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('fen', $puzzle->fen) }}</textarea>
                    @error('fen') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $puzzle->description) }}</textarea>
                    @error('description') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="pt-2">
                    <label class="inline-flex items-center cursor-pointer">
                        <input type="checkbox" name="is_public" value="1" {{ old('is_public', $puzzle->is_public) ? 'checked' : '' }} class="sr-only peer">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-indigo-600 relative"></div>
                        <span class="ml-3 text-sm font-medium text-gray-700">Public Visibility</span>
                    </label>
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('admin.puzzles.index') }}" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Update Puzzle</button>
            </div>
        </form>
    </div>
</div>
@endsection
