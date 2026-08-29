@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
    <!-- Header Title & Stats -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 pb-4 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <h1 class="text-lg font-bold text-gray-900">Users</h1>
            <span class="px-3 py-1 text-xs font-semibold bg-indigo-50 text-indigo-700 rounded-full border border-indigo-100">
                Total: {{ number_format($users->total()) }}
            </span>
        </div>

        <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition flex items-center justify-center gap-2">
            <i class="fa-solid fa-user-plus"></i> Add User
        </a>
    </div>

    <!-- Header Controls -->
    <div class="flex flex-col sm:flex-row justify-between gap-4 mb-6">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or email..." class="px-4 py-2 border rounded-lg text-sm w-64 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            <select name="role" class="px-4 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                <option value="">All Roles</option>
                <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admins</option>
                <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Standard Users</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-800 text-white text-sm rounded-lg hover:bg-gray-700 transition">Filter</button>
        </form>
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b bg-gray-50 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                    <th class="p-4">User</th>
                    <th class="p-4">Elo</th>
                    <th class="p-4">Gems</th>
                    <th class="p-4">Role</th>
                    <th class="p-4">Plan</th>
                    <th class="p-4">Joined</th>
                    <th class="p-4 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y text-sm text-gray-700">
                @forelse ($users as $user)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="p-4 flex items-center gap-3">
                            <img class="w-8 h-8 rounded-full border object-cover" src="{{ $user->getAvatarUrl() }}" alt="">
                            <div>
                                <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                <p class="text-xs text-gray-400">{{ $user->email }}</p>
                            </div>
                        </td>
                        <td class="p-4 font-medium">{{ number_format($user->elo) }}</td>
                        <td class="p-4 font-medium">
                            <span class="inline-flex items-center gap-1.5 text-emerald-700">
                                <i class="fa-solid fa-gem text-emerald-500 text-xs"></i>
                                {{ number_format($user->gems ?? 0) }}
                            </span>
                        </td>
                        <td class="p-4">
                            @if ($user->is_admin)
                                <span class="px-2 py-1 text-xs font-semibold text-purple-700 bg-purple-100 rounded-full">Admin</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold text-gray-600 bg-gray-100 rounded-full">User</span>
                            @endif
                        </td>
                        <td class="p-4 uppercase text-xs font-semibold text-gray-500">
                            {{ $user->subscription_plan ?? 'Free' }}
                        </td>
                        <td class="p-4 text-xs text-gray-500">{{ $user->created_at->format('M d, Y') }}</td>
                        <td class="p-4 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.users.edit', $user) }}" class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-lg transition"><i class="fa-solid fa-pen-to-square"></i></a>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-8 text-center text-gray-400">No users found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination & Footer Info -->
    <div class="mt-6 flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-gray-500">
        <div>
            Showing {{ $users->firstItem() ?? 0 }} to {{ $users->lastItem() ?? 0 }} of {{ number_format($users->total()) }} users
        </div>
        <div>
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection