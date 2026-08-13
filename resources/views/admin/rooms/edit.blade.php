@extends('layouts.admin')

@section('title', 'Edit Room')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Active Timers Preview -->
    <div class="grid grid-cols-2 gap-4">
        <div class="bg-white p-4 rounded-xl border border-rose-100 text-center shadow-sm">
            <p class="text-xs text-rose-500 font-semibold uppercase">Red Time Remaining</p>
            <p class="text-2xl font-bold text-rose-700 mt-1">{{ number_format($room->getCalculatedTimes()['red_time'], 1) }}s</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-gray-200 text-center shadow-sm">
            <p class="text-xs text-gray-500 font-semibold uppercase">Black Time Remaining</p>
            <p class="text-2xl font-bold text-gray-800 mt-1">{{ number_format($room->getCalculatedTimes()['black_time'], 1) }}s</p>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white p-8 rounded-xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-lg font-semibold text-gray-800">Edit Room: <span class="font-mono text-indigo-600">{{ $room->code }}</span></h2>
            <a href="{{ route('admin.rooms.index') }}" class="text-sm text-gray-500 hover:text-gray-700">
                <i class="fa-solid fa-arrow-left mr-1"></i> Back
            </a>
        </div>

        <form action="{{ route('admin.rooms.update', $room->code) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Room Name</label>
                        <input type="text" name="name" value="{{ old('name', $room->name) }}" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Result <span class="text-xs text-gray-400 font-normal">(1=Red, -1=Black, 0=Draw)</span></label>
                        <input type="text" name="result" value="{{ old('result', $room->result) }}" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('result') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current FEN Notation</label>
                    <textarea name="fen" rows="2" required class="w-full px-4 py-2 border rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('fen', $room->fen) }}</textarea>
                    @error('fen') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Host User ID</label>
                        <input type="number" name="host_id" value="{{ old('host_id', $room->host_id) }}" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('host_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Guest User ID</label>
                        <input type="number" name="guest_id" value="{{ old('guest_id', $room->guest_id) }}" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('guest_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Red Time (Seconds)</label>
                        <input type="number" step="0.1" name="red_time" value="{{ old('red_time', $room->red_time) }}" required class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('red_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Black Time (Seconds)</label>
                        <input type="number" step="0.1" name="black_time" value="{{ old('black_time', $room->black_time) }}" required class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('black_time') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Active Player State</label>
                        <input type="text" name="active_player" value="{{ old('active_player', $room->active_player) }}" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @error('active_player') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password Override</label>
                    <input type="text" name="pass" value="{{ old('pass', $room->pass) }}" class="w-full px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    @error('pass') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-8 flex justify-end gap-3">
                <a href="{{ route('admin.rooms.index') }}" class="px-4 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700">Update Room</button>
            </div>
        </form>
    </div>
</div>
@endsection
