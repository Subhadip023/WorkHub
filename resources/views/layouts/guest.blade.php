<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? 'WorkHub' }} - Collaborative Workspace</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('asset/img/logo.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="font-sans text-slate-200 bg-[#090d16] antialiased min-h-screen flex flex-col justify-between selection:bg-indigo-500 selection:text-white relative overflow-x-hidden">
    <!-- Subtle Background Glows -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[400px] bg-gradient-to-b from-indigo-500/15 via-purple-500/10 to-transparent blur-3xl opacity-70"></div>
        <div class="absolute top-1/3 -right-40 w-[450px] h-[350px] bg-blue-500/10 blur-3xl rounded-full"></div>
        <div class="absolute bottom-10 -left-40 w-[450px] h-[350px] bg-purple-500/10 blur-3xl rounded-full"></div>
    </div>

    <!-- Header Navigation -->
    <header class="w-full border-b border-slate-800/60 bg-slate-950/40 backdrop-blur-md sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="w-8 h-8 transition-transform group-hover:scale-105 duration-200">
                    <defs>
                        <linearGradient id="hub-nav-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#6366f1" />
                            <stop offset="100%" stop-color="#a855f7" />
                        </linearGradient>
                    </defs>
                    <path d="M16 2 L28 9 L28 23 L16 30 L4 23 L4 9 Z" fill="none" stroke="url(#hub-nav-grad)" stroke-width="2.5" stroke-linejoin="round" />
                    <path d="M16 8 L23 12 L23 20 L16 24 L9 20 L9 12 Z" fill="url(#hub-nav-grad)" opacity="0.9" />
                    <circle cx="16" cy="16" r="3.5" fill="#ffffff" />
                </svg>
                <span class="font-bold text-xl tracking-tight text-white group-hover:text-indigo-400 transition-colors">WorkHub</span>
            </a>

            <!-- Dual-Engine Badge in Navbar -->
            <div class="hidden sm:inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-slate-900/80 text-slate-300 border border-slate-800/80 shadow-inner">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Dual-Engine: Developer & Classic Modes</span>
            </div>

            <div class="flex items-center gap-4 text-sm font-medium">
                <a href="{{ url('/') }}" class="text-slate-400 hover:text-white transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Back to Home</span>
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content Slot -->
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6 lg:p-8">
        <div class="w-full max-w-xl">
            <!-- Mobile Badge -->
            <div class="sm:hidden flex justify-center mb-6">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-slate-900/80 text-slate-300 border border-slate-800">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Dual-Engine: Developer & Classic</span>
                </div>
            </div>

            {{ $slot }}
        </div>
    </main>

    <!-- Footer -->
    <footer class="w-full py-6 border-t border-slate-800/40 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            <p>&copy; {{ date('Y') }} WorkHub. Unified data layer with dual interfaces. Switch modes anytime in Profile Settings.</p>
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
