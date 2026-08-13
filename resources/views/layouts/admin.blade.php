<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard')</title>

    <!-- Tailwind CSS & FontAwesome CDNs -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans leading-normal tracking-normal">

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-slate-900 text-slate-100 flex flex-col justify-between shadow-lg">
            <div>
                <div class="p-6 border-b border-slate-800 flex items-center gap-3">
                    <i class="fa-solid fa-chess-king text-amber-500 text-2xl"></i>
                    <span class="text-xl font-bold tracking-wide">Admin Panel</span>
                </div>
                <nav class="mt-6 px-4 space-y-2">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-600 text-white' : 'text-slate-300' }}">
                        <i class="fa-solid fa-chart-line w-5"></i> Dashboard
                    </a>
                    <a href="{{ route('admin.users.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('admin.users.*') ? 'bg-indigo-600 text-white' : 'text-slate-300' }}">
                        <i class="fa-solid fa-users w-5"></i> Users
                    </a>
                </nav>
            </div>
            <div class="p-4 border-t border-slate-800">
                <a href="{{ url('/') }}" class="flex items-center gap-3 px-4 py-2 text-slate-400 hover:text-white transition">
                    <i class="fa-solid fa-arrow-left"></i> Back to Game
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            <!-- Navbar -->
            <header class="bg-white shadow-sm border-b px-8 py-4 flex justify-between items-center">
                <h1 class="text-xl font-semibold text-gray-800">@yield('title')</h1>
                <div class="flex items-center gap-4">
                    <span class="text-sm font-medium text-gray-600">{{ auth()->user()->name }}</span>
                    <img class="w-9 h-9 rounded-full object-cover border" src="{{ auth()->user()->getAvatarUrl() }}" alt="Avatar">
                </div>
            </header>

            <!-- Dynamic Flash Alert -->
            <main class="p-8 flex-1">
                @if (session('success'))
                    <div class="mb-6 p-4 bg-emerald-100 border border-emerald-300 text-emerald-800 rounded-lg flex justify-between items-center">
                        <span><i class="fa-solid fa-circle-check mr-2"></i>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 p-4 bg-rose-100 border border-rose-300 text-rose-800 rounded-lg flex justify-between items-center">
                        <span><i class="fa-solid fa-circle-exclamation mr-2"></i>{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
