<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta property="og:image" content="@yield('og_image', url('/img/1200x630.jpg'))">
    <meta property="og:image:width" content="@yield('og_image_width', '1200')" >
    <meta property="og:image:height" content="@yield('og_image_height', '630')" >
    <meta property="og:image:alt" content="@yield('og_image_alt', 'Cờ tướng 2 người')" >
    <meta property="og:image:type" content="@yield('og_image_type', 'image/jpeg')" />

    <title>@yield('title', 'Admin Dashboard')</title>

    <link rel="apple-touch-icon" href="{{ asset('img/app-icons/apple-touch-icon-iphone-game.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/app-icons/apple-touch-icon-ipad-game.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('img/app-icons/apple-touch-icon-iphone-retina-game.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('img/app-icons/apple-touch-icon-ipad-retina-game.png') }}">
    <link rel="icon" sizes="32x32" href="{{ asset('img/favicon-32x32-game.png') }}" >

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
                    <a href="{{ route('admin.rooms.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('admin.rooms.*') ? 'bg-indigo-600 text-white' : 'text-slate-300' }}">
                        <i class="fa-solid fa-gamepad w-5"></i> Rooms
                    </a>
                    <a href="{{ route('admin.puzzles.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('admin.puzzles.*') ? 'bg-indigo-600 text-white' : 'text-slate-300' }}">
                        <i class="fa-solid fa-puzzle-piece w-5"></i> Puzzles
                    </a>
                    <a href="{{ route('admin.tournaments.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-slate-800 transition {{ request()->routeIs('admin.tournaments.*') ? 'bg-indigo-600 text-white' : 'text-slate-300' }}">
                        <i class="fa-solid fa-trophy w-5"></i> Tournaments
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
