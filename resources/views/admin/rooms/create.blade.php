@extends('layouts.admin')

@section('title', 'Create Room')

@section('content')
<div class="max-w-3xl mx-auto bg-white p-8 rounded-xl shadow-sm border border-gray-100">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-semibold text-gray-800">Create New Room</h2>
        <a href="{{ route('admin.rooms.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
            <i class="fa-solid fa-arrow-left mr-1"></i> Back
        </a>
    </div>

    <form action="{{ route('admin.rooms.store') }}" method="POST">
        @csrf

        <div class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Room Code <span class="text-xs text-gray-400 font-normal">(Leave blank to auto-generate)</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" class="w-full px-4 py-2 border rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('code') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Room Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" placeholder="Casual Match" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Starting FEN Notation</label>
                <input type="text" name="fen" value="{{ old('fen', 'rnbakabnr/9/1c5c1/p1p1p1p1p/9/9/P1P1P1P1P/1C5C1/9/RNBAKABNR w - - 0 1') }}" required class="w-full px-4 py-2 border rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('fen') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Host User ID <span class="text-xs text-gray-400 font-normal">(Optional)</span></label>
                    <input type="number" name="host_id" value="{{ old('host_id') }}" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('host_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Guest User ID <span class="text-xs text-gray-400 font-normal">(Optional)</span></label>
                    <input type="number" name="guest_id" value="{{ old('guest_id') }}" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('guest_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Red Time (Seconds)</label>
                    <input type="number" step="0.1" name="red_time" value="{{ old('red_time', 600) }}" required class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('red_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Black Time (Seconds)</label>
                    <input type="number" step="0.1" name="black_time" value="{{ old('black_time', 600) }}" required class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('black_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Initial Active Player</label>
                    <select name="active_player" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="waiting" {{ old('active_player') == 'waiting' ? 'selected' : '' }}>Waiting</option>
                        <option value="red" {{ old('active_player') == 'red' ? 'selected' : '' }}>Red</option>
                        <option value="black" {{ old('active_player') == 'black' ? 'selected' : '' }}>Black</option>
                    </select>
                    @error('active_player') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Password <span class="text-xs text-gray-400 font-normal">(Optional)</span></label>
                <input type="text" name="pass" value="{{ old('pass') }}" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                @error('pass') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="mt-8 flex justify-end gap-3">
            <a href="{{ route('admin.rooms.index') }}" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Create Room</button>
        </div>
    </form>
</div>
@endsection
