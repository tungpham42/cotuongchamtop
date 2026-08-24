<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta property="og:image" content="@yield('og_image', url('/img/1200x630.jpg'))">
    <meta property="og:image:width" content="@yield('og_image_width', '1200')">
    <meta property="og:image:height" content="@yield('og_image_height', '630')">
    <meta property="og:image:alt" content="@yield('og_image_alt', 'Cờ tướng 2 người')">
    <meta property="og:image:type" content="@yield('og_image_type', 'image/jpeg')">

    <title>@yield('title', 'Admin Dashboard')</title>

    <link rel="apple-touch-icon" href="{{ asset('img/app-icons/apple-touch-icon-iphone-game.png') }}">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('img/app-icons/apple-touch-icon-ipad-game.png') }}">
    <link rel="apple-touch-icon" sizes="120x120" href="{{ asset('img/app-icons/apple-touch-icon-iphone-retina-game.png') }}">
    <link rel="apple-touch-icon" sizes="152x152" href="{{ asset('img/app-icons/apple-touch-icon-ipad-retina-game.png') }}">
    <link rel="icon" sizes="32x32" href="{{ asset('img/favicon-32x32-game.png') }}">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                    },
                    boxShadow: {
                        soft: '0 12px 40px rgba(15, 23, 42, .08)',
                        lift: '0 18px 50px rgba(79, 70, 229, .16)',
                    }
                }
            }
        };
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        html { scroll-behavior: smooth; }
        body { font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        .admin-bg {
            background:
                radial-gradient(circle at 15% 15%, rgba(99,102,241,.12), transparent 28%),
                radial-gradient(circle at 90% 10%, rgba(14,165,233,.10), transparent 24%),
                #f8fafc;
        }
        .glass { background: rgba(255,255,255,.82); backdrop-filter: blur(16px); }
        .sidebar-gradient {
            background:
                radial-gradient(circle at 15% 10%, rgba(129,140,248,.22), transparent 28%),
                radial-gradient(circle at 90% 70%, rgba(14,165,233,.12), transparent 30%),
                linear-gradient(180deg, #111827 0%, #0f172a 100%);
        }
        .nav-active { box-shadow: inset 3px 0 0 #a5b4fc; }
        .fade-up { animation: fadeUp .45s ease both; }
        @keyframes fadeUp { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        @media (prefers-reduced-motion: reduce) {
            .fade-up { animation: none; }
        }
    </style>
</head>

<body class="admin-bg text-slate-800 antialiased">
    <div class="min-h-screen flex">

        <div id="adminOverlay" class="fixed inset-0 bg-slate-950/50 z-30 hidden lg:hidden"></div>

        <aside id="adminSidebar"
               class="sidebar-gradient fixed lg:sticky top-0 left-0 z-40 h-screen w-[280px] shrink-0 text-slate-200
                      -translate-x-full lg:translate-x-0 transition-transform duration-300 flex flex-col">
            <div class="px-5 pt-6">
                <div class="flex items-center gap-3 px-2 mb-8">
                    <div class="h-11 w-11 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center shadow-lg">
                        <i class="fa-solid fa-chess-king text-amber-300 text-xl"></i>
                    </div>
                    <div>
                        <div class="font-extrabold tracking-tight text-white text-lg">Cờ Tướng</div>
                        <div class="text-[11px] uppercase tracking-[.22em] text-slate-400">Admin Console</div>
                    </div>
                </div>

                <div class="px-3 mb-3 text-[11px] font-bold uppercase tracking-[.18em] text-slate-500">Workspace</div>
                <nav class="space-y-1.5">
                    <a href="{{ route('admin.dashboard') }}"
                       class="group flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition
                       {{ request()->routeIs('admin.dashboard') ? 'bg-white/10 text-white nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.dashboard') ? 'bg-indigo-500/20 text-indigo-200' : 'bg-white/5 text-slate-400 group-hover:text-white' }}">
                            <i class="fa-solid fa-chart-pie text-sm"></i>
                        </span>
                        Dashboard
                    </a>

                    <a href="{{ route('admin.users.index') }}"
                       class="group flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition
                       {{ request()->routeIs('admin.users.*') ? 'bg-white/10 text-white nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.users.*') ? 'bg-sky-500/20 text-sky-200' : 'bg-white/5 text-slate-400 group-hover:text-white' }}">
                            <i class="fa-solid fa-users text-sm"></i>
                        </span>
                        Users
                    </a>

                    <a href="{{ route('admin.rooms.index') }}"
                       class="group flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition
                       {{ request()->routeIs('admin.rooms.*') ? 'bg-white/10 text-white nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.rooms.*') ? 'bg-emerald-500/20 text-emerald-200' : 'bg-white/5 text-slate-400 group-hover:text-white' }}">
                            <i class="fa-solid fa-chess-board text-sm"></i>
                        </span>
                        Rooms
                    </a>

                    <a href="{{ route('admin.puzzles.index') }}"
                       class="group flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition
                       {{ request()->routeIs('admin.puzzles.*') ? 'bg-white/10 text-white nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.puzzles.*') ? 'bg-fuchsia-500/20 text-fuchsia-200' : 'bg-white/5 text-slate-400 group-hover:text-white' }}">
                            <i class="fa-solid fa-puzzle-piece text-sm"></i>
                        </span>
                        Puzzles
                    </a>

                    <a href="{{ route('admin.tournaments.index') }}"
                       class="group flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition
                       {{ request()->routeIs('admin.tournaments.*') ? 'bg-white/10 text-white nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.tournaments.*') ? 'bg-amber-500/20 text-amber-200' : 'bg-white/5 text-slate-400 group-hover:text-white' }}">
                            <i class="fa-solid fa-trophy text-sm"></i>
                        </span>
                        Tournaments
                    </a>

                    <a href="{{ route('admin.articles.index') }}"
                       class="group flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm font-semibold transition
                       {{ request()->routeIs('admin.articles.*') ? 'bg-white/10 text-white nav-active' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <span class="w-8 h-8 rounded-lg flex items-center justify-center {{ request()->routeIs('admin.articles.*') ? 'bg-rose-500/20 text-rose-200' : 'bg-white/5 text-slate-400 group-hover:text-white' }}">
                            <i class="fa-solid fa-newspaper text-sm"></i>
                        </span>
                        Articles
                    </a>
                </nav>
            </div>

            <div class="mt-auto p-5">
                <div class="rounded-2xl border border-white/10 bg-white/5 p-4 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="h-9 w-9 rounded-xl bg-emerald-400/10 flex items-center justify-center text-emerald-300">
                            <i class="fa-solid fa-circle text-[9px]"></i>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-white">System online</div>
                            <div class="text-xs text-slate-400">Admin services available</div>
                        </div>
                    </div>
                </div>
                <a href="{{ url('/') }}" class="flex items-center gap-3 px-3 py-2 text-sm text-slate-400 hover:text-white transition">
                    <span class="w-8 text-center"><i class="fa-solid fa-arrow-left"></i></span>
                    Back to game
                </a>
            </div>
        </aside>

        <div class="min-w-0 flex-1 flex flex-col">
            <header class="sticky top-0 z-20 glass border-b border-slate-200/80">
                <div class="h-20 px-4 sm:px-6 lg:px-8 flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <button id="adminMenuButton" type="button"
                                class="lg:hidden h-10 w-10 rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50"
                                aria-label="Open navigation">
                            <i class="fa-solid fa-bars"></i>
                        </button>
                        <div class="min-w-0">
                            <div class="text-[11px] uppercase tracking-[.18em] font-bold text-slate-400">Cờ Tướng · Control Center</div>
                            <h1 class="text-base sm:text-lg font-extrabold text-slate-900 truncate">@yield('title')</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <div class="hidden md:flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 text-xs font-semibold text-slate-500">
                            <i class="fa-regular fa-clock"></i>
                            <span id="adminClock">--:--</span>
                        </div>
                        <div class="hidden sm:block h-8 w-px bg-slate-200"></div>
                        <div class="flex items-center gap-3">
                            <div class="hidden sm:block text-right">
                                <div class="text-sm font-bold text-slate-800 leading-tight">{{ auth()->user()->name }}</div>
                                <div class="text-xs text-slate-400">Administrator</div>
                            </div>
                            <img class="w-11 h-11 rounded-2xl object-cover border-2 border-white shadow-sm ring-1 ring-slate-200"
                                 src="{{ auth()->user()->getAvatarUrl() }}" alt="Avatar">
                        </div>
                    </div>
                </div>
            </header>

            <main class="p-4 sm:p-6 lg:p-8 flex-1">
                @if (session('success'))
                    <div class="fade-up mb-6 rounded-2xl border border-emerald-200 bg-emerald-50/90 px-4 py-3.5 text-emerald-800 shadow-sm flex items-center gap-3">
                        <span class="h-9 w-9 rounded-xl bg-emerald-100 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-check"></i>
                        </span>
                        <span class="text-sm font-semibold">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="fade-up mb-6 rounded-2xl border border-rose-200 bg-rose-50/90 px-4 py-3.5 text-rose-800 shadow-sm flex items-center gap-3">
                        <span class="h-9 w-9 rounded-xl bg-rose-100 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </span>
                        <span class="text-sm font-semibold">{{ session('error') }}</span>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('adminOverlay');
            const button = document.getElementById('adminMenuButton');

            const setSidebar = (open) => {
                sidebar?.classList.toggle('-translate-x-full', !open);
                overlay?.classList.toggle('hidden', !open);
                document.body.classList.toggle('overflow-hidden', open);
            };

            button?.addEventListener('click', () => setSidebar(true));
            overlay?.addEventListener('click', () => setSidebar(false));

            document.querySelectorAll('#adminSidebar a').forEach((link) => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 1024) setSidebar(false);
                });
            });

            const clock = document.getElementById('adminClock');
            const updateClock = () => {
                if (!clock) return;
                clock.textContent = new Intl.DateTimeFormat(undefined, {
                    hour: '2-digit',
                    minute: '2-digit'
                }).format(new Date());
            };
            updateClock();
            setInterval(updateClock, 30000);
        });
    </script>

    @stack('scripts')
</body>
</html>
