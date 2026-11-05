<!DOCTYPE html>
<html lang="en" class="scroll-smooth dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <!-- SEO Optimization -->
    <title>WorkHub - Collaborative Workspace with Dual-Engine Developer & Classic Modes</title>
    <meta name="description" content="WorkHub is the collaborative workspace for modern teams featuring a Dual-Engine architecture: high-velocity Developer Mode (Inertia React) and ultra-lightweight Classic Mode (Blade SSR).">
    <meta name="keywords" content="project tracking, dual-engine, developer tools, task manager, team collaboration, inertia react, laravel blade, kanban, velocity analytics">
    <meta name="author" content="WorkHub Team">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:title" content="WorkHub - Dual-Engine Project Tracking & Workspace Platform">
    <meta property="og:description" content="WorkHub offers a unified workspace with two distinct interfaces: Modern Developer Mode (React SPA) and Classic Mode (Blade SSR).">
    
    <!-- Favicon & Fonts -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('asset/img/logo.svg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <!-- Vite Assets (Tailwind CSS) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Pre-render Theme Initialization -->
    <script>
        (function() {
            const savedTheme = localStorage.getItem('workhub_theme') || 'dark';
            if (savedTheme === 'light') {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
            } else {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            }
        })();
    </script>
</head>
<body class="font-sans bg-[#090d16] text-slate-200 antialiased selection:bg-indigo-500 selection:text-white relative overflow-x-hidden">

    <!-- Background Ambient Glows -->
    <div class="fixed inset-0 pointer-events-none -z-10 overflow-hidden">
        <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[850px] h-[450px] bg-gradient-to-b from-indigo-500/15 via-purple-500/10 to-transparent blur-3xl opacity-80"></div>
        <div class="absolute top-[30%] -right-32 w-[550px] h-[450px] bg-blue-500/10 blur-3xl rounded-full"></div>
        <div class="absolute top-[65%] -left-32 w-[550px] h-[450px] bg-purple-500/10 blur-3xl rounded-full"></div>
    </div>

    <!-- Header Navigation -->
    <header class="fixed top-0 left-0 right-0 z-50 border-b border-slate-800/60 bg-slate-950/70 backdrop-blur-xl">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="w-8 h-8 transition-transform group-hover:scale-105 duration-200">
                        <defs>
                            <linearGradient id="nav-logo-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#6366f1" />
                                <stop offset="100%" stop-color="#a855f7" />
                            </linearGradient>
                        </defs>
                        <path d="M16 2 L28 9 L28 23 L16 30 L4 23 L4 9 Z" fill="none" stroke="url(#nav-logo-grad)" stroke-width="2.5" stroke-linejoin="round" />
                        <path d="M16 8 L23 12 L23 20 L16 24 L9 20 L9 12 Z" fill="url(#nav-logo-grad)" opacity="0.9" />
                        <circle cx="16" cy="16" r="3.5" fill="#ffffff" />
                    </svg>
                    <span class="font-bold text-xl tracking-tight text-white group-hover:text-indigo-400 transition-colors">WorkHub</span>
                </a>

                <!-- Dual-Engine Navbar Badge -->
                <div class="hidden lg:inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-slate-900/90 text-slate-300 border border-slate-800 shadow-inner">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span>Dual-Engine: Developer &amp; Classic Modes</span>
                </div>
            </div>

            <!-- Nav Links -->
            <nav class="hidden md:flex items-center gap-8 text-sm font-medium text-slate-400">
                <a href="#dual-engine" class="hover:text-white transition-colors">Dual Modes</a>
                <a href="#bento" class="hover:text-white transition-colors">Features</a>
                <a href="#workflow" class="hover:text-white transition-colors">Workflow</a>
                <a href="{{ route('routes.index') }}" class="hover:text-white transition-colors">Routes Parity</a>
            </nav>

            <!-- Auth Buttons & Topbar Theme Button -->
            <div class="flex items-center gap-3">
                <!-- Topbar Theme Button (Sun / Moon) -->
                <button type="button" onclick="toggleLandingTheme()" id="landingThemeBtn" class="p-2 rounded-lg bg-slate-900/80 border border-slate-800 text-slate-300 hover:text-white hover:bg-slate-800 transition-colors cursor-pointer" title="Toggle Theme (Light / Dark)">
                    <svg id="themeSunIcon" class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    <svg id="themeMoonIcon" class="w-4 h-4 text-slate-400 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/></svg>
                </button>

                @auth
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-semibold shadow-lg shadow-indigo-500/25 transition-all flex items-center gap-2">
                        <span>Go to Dashboard</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-white/20 uppercase font-mono font-bold">{{ auth()->user()->ui_version }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('login') }}" class="px-3.5 py-2 text-sm font-medium text-slate-300 hover:text-white transition-colors">
                        Sign In
                    </a>
                    <a href="{{ route('register') }}" class="px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white text-sm font-semibold shadow-lg shadow-indigo-500/25 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                        Get Started
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="pt-28 sm:pt-32">
        <!-- Hero Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-20 text-center">
            
            <!-- Dual-Engine Top Pill Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs sm:text-sm font-medium bg-indigo-500/10 text-indigo-300 border border-indigo-500/25 mb-6 shadow-inner animate-fade-in">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>WorkHub Dual-Engine Architecture • Choice of Modern React SPA or Pure Blade SSR</span>
            </div>

            <!-- Headline -->
            <h1 class="text-4xl sm:text-6xl lg:text-7xl font-extrabold tracking-tight text-white max-w-5xl mx-auto leading-[1.12]">
                One Platform. <br class="hidden sm:inline" />
                <span class="bg-gradient-to-r from-indigo-400 via-purple-300 to-pink-400 bg-clip-text text-transparent">Two Workflows.</span> Tailored for Every Team.
            </h1>

            <!-- Subtitle -->
            <p class="mt-6 text-lg sm:text-xl text-slate-400 max-w-3xl mx-auto leading-relaxed">
                Whether you prefer the interactive cockpit of <strong class="text-indigo-400 font-semibold">Developer Mode</strong> (Inertia React) or the distraction-free speed of <strong class="text-slate-200 font-semibold">Classic Mode</strong> (Blade SSR), WorkHub serves both seamlessly from the exact same database.
            </p>

            <!-- Hero Interactive Mode Selector Pill -->
            <div class="mt-8 inline-flex items-center p-1.5 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl">
                <button type="button" id="hero-btn-v2" onclick="setLandingPreview('v2')"
                    class="px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 transition-all cursor-pointer bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/25">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Developer Mode (V2 React)</span>
                </button>
                <button type="button" id="hero-btn-v1" onclick="setLandingPreview('v1')"
                    class="px-5 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-400 hover:text-white flex items-center gap-2 transition-all cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    <span>Classic Mode (V1 Blade)</span>
                </button>
            </div>

            <!-- Hero Action Buttons -->
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                @auth
                    <a href="{{ route('dashboard') }}" class="px-6 py-3.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-semibold text-base shadow-xl shadow-indigo-500/30 transition-all transform hover:-translate-y-0.5 flex items-center gap-2.5">
                        <span>Open Your Workspace ({{ auth()->user()->ui_version }})</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                @else
                    <a href="{{ route('register') }}" class="px-6 py-3.5 rounded-xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-semibold text-base shadow-xl shadow-indigo-500/30 transition-all transform hover:-translate-y-0.5 flex items-center gap-2.5">
                        <span>Get Started Free</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </a>
                    <a href="#dual-engine" class="px-6 py-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-300 hover:text-white border border-slate-800 font-semibold text-base transition-colors flex items-center gap-2">
                        <span>Compare Architecture</span>
                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </a>
                @endauth
            </div>

            <!-- Value Proposition Badges -->
            <div class="mt-14 pt-10 border-t border-slate-800/60 grid grid-cols-2 sm:grid-cols-4 gap-6 max-w-4xl mx-auto text-left">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7C5 4 4 5 4 7z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400">Database</div>
                        <div class="text-sm font-semibold text-white">Single Shared Store</div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-purple-500/10 border border-purple-500/20 flex items-center justify-center text-purple-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400">Routing</div>
                        <div class="text-sm font-semibold text-white">Clean Unaltered URLs</div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400">Security</div>
                        <div class="text-sm font-semibold text-white">PBAC Policy Driven</div>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <div>
                        <div class="text-xs text-slate-400">Flexibility</div>
                        <div class="text-sm font-semibold text-white">1-Click Mode Toggle</div>
                    </div>
                </div>
            </div>

            <!-- Dynamic Live Hero Mockup Frame -->
            <div class="mt-14 max-w-5xl mx-auto">
                <!-- V2 Mockup Canvas (Developer Cockpit) -->
                <div id="hero-mockup-v2" class="rounded-2xl border border-slate-800 bg-slate-900/90 shadow-2xl shadow-indigo-500/10 overflow-hidden text-left transition-all duration-300">
                    <div class="px-4 py-3 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between text-xs text-slate-400">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-rose-500/80"></span>
                            <span class="w-3 h-3 rounded-full bg-amber-500/80"></span>
                            <span class="w-3 h-3 rounded-full bg-emerald-500/80"></span>
                        </div>
                        <div class="px-3 py-1 rounded-md bg-slate-900 border border-slate-800/80 font-mono text-[11px] text-slate-300">
                            workhub.io/projects/10 • Developer React Cockpit
                        </div>
                        <span class="text-[10px] uppercase font-bold text-indigo-400 bg-indigo-500/10 px-2 py-0.5 rounded border border-indigo-500/20">V2 Developer</span>
                    </div>

                    <div class="p-6 bg-[#0b0f19] space-y-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <div class="text-xs text-indigo-400 font-semibold uppercase tracking-wider">Project Hub • Active Sprint</div>
                                <h4 class="text-xl font-bold text-white">E-Commerce Mobile API Platform</h4>
                            </div>
                            <span class="px-3 py-1 text-xs rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">85% Velocity Complete</span>
                        </div>

                        <!-- Mini KPI Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="p-3.5 rounded-lg bg-slate-900/80 border border-slate-800">
                                <div class="text-[11px] text-slate-400">Tasks Completed</div>
                                <div class="text-xl font-bold text-white font-mono mt-1">24 / 28</div>
                            </div>
                            <div class="p-3.5 rounded-lg bg-slate-900/80 border border-slate-800">
                                <div class="text-[11px] text-slate-400">Burndown Velocity</div>
                                <div class="text-xl font-bold text-indigo-400 font-mono mt-1">+18.4%</div>
                            </div>
                            <div class="p-3.5 rounded-lg bg-slate-900/80 border border-slate-800">
                                <div class="text-[11px] text-slate-400">Credential Vault</div>
                                <div class="text-xl font-bold text-purple-400 font-mono mt-1">4 Vaulted Keys</div>
                            </div>
                        </div>

                        <!-- Kanban Columns Simulation -->
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                            <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2">
                                <div class="text-xs font-semibold text-slate-400 flex items-center justify-between">
                                    <span>IN PROGRESS (2)</span>
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                </div>
                                <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 text-xs">
                                    <div class="font-medium text-white">Auth Token Refresh Endpoint</div>
                                    <div class="text-[10px] text-indigo-400 mt-1 flex items-center gap-1.5">
                                        <span>#BE-102</span> • <span class="text-rose-400 font-semibold">Urgent</span>
                                    </div>
                                </div>
                            </div>
                            <div class="p-3.5 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2">
                                <div class="text-xs font-semibold text-slate-400 flex items-center justify-between">
                                    <span>COMPLETED (24)</span>
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                </div>
                                <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 text-xs opacity-80">
                                    <div class="font-medium text-slate-300 line-through">Stripe Webhook Gateway</div>
                                    <div class="text-[10px] text-emerald-400 mt-1">Verified &amp; Deployed</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- V1 Mockup Canvas (Classic Blade Workflow) -->
                <div id="hero-mockup-v1" class="hidden rounded-2xl border border-slate-800 bg-slate-900/90 shadow-2xl shadow-blue-500/10 overflow-hidden text-left transition-all duration-300">
                    <div class="px-4 py-3 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between text-xs text-slate-400">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-blue-500/80"></span>
                            <span class="w-3 h-3 rounded-full bg-slate-600"></span>
                            <span class="w-3 h-3 rounded-full bg-slate-600"></span>
                        </div>
                        <div class="px-3 py-1 rounded-md bg-slate-900 border border-slate-800/80 font-mono text-[11px] text-slate-300">
                            workhub.io/tasks • Classic Blade SSR Grid
                        </div>
                        <span class="text-[10px] uppercase font-bold text-blue-400 bg-blue-500/10 px-2 py-0.5 rounded border border-blue-500/20">V1 Classic</span>
                    </div>

                    <div class="p-6 bg-[#0b0f19] space-y-4">
                        <div class="flex items-center justify-between flex-wrap gap-2">
                            <div>
                                <div class="text-xs text-blue-400 font-semibold uppercase tracking-wider">Enterprise Workspace • Dokan Sathi</div>
                                <h4 class="text-xl font-bold text-white">Task Management Spreadsheet</h4>
                            </div>
                            <span class="px-3 py-1 text-xs rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20 font-medium">⚡ Zero Hydration Lag • Instant SSR</span>
                        </div>

                        <!-- KPI Summary Cards -->
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="p-3.5 rounded-lg bg-slate-900/80 border border-slate-800 border-l-4 border-l-emerald-500">
                                <div class="text-[11px] font-semibold text-slate-400 uppercase">Active Projects</div>
                                <div class="text-xl font-bold text-white mt-1">12 Projects</div>
                            </div>
                            <div class="p-3.5 rounded-lg bg-slate-900/80 border border-slate-800 border-l-4 border-l-blue-500">
                                <div class="text-[11px] font-semibold text-slate-400 uppercase">Completion Rate</div>
                                <div class="text-xl font-bold text-white mt-1">84% Finished</div>
                            </div>
                            <div class="p-3.5 rounded-lg bg-slate-900/80 border border-slate-800 border-l-4 border-l-amber-500">
                                <div class="text-[11px] font-semibold text-slate-400 uppercase">Pending Review</div>
                                <div class="text-xl font-bold text-white mt-1">4 High Priority</div>
                            </div>
                        </div>

                        <!-- Classic Inline Spreadsheet Table -->
                        <div class="rounded-lg border border-slate-800 overflow-hidden text-xs">
                            <div class="grid grid-cols-12 bg-slate-950/80 p-2.5 font-semibold text-slate-400 border-b border-slate-800">
                                <div class="col-span-2">Status</div>
                                <div class="col-span-5">Task Title</div>
                                <div class="col-span-3">Priority</div>
                                <div class="col-span-2 text-right">Assignee</div>
                            </div>
                            <div class="grid grid-cols-12 p-2.5 bg-slate-900/60 border-b border-slate-800/50 items-center">
                                <div class="col-span-2 text-emerald-400 font-semibold flex items-center gap-1.5">
                                    <span>☑</span> <span>Done</span>
                                </div>
                                <div class="col-span-5 text-slate-400 line-through">Database Migration &amp; Model Seeders</div>
                                <div class="col-span-3"><span class="px-2 py-0.5 rounded bg-blue-500/10 text-blue-300 text-[10px] font-semibold border border-blue-500/20">Normal</span></div>
                                <div class="col-span-2 text-right text-slate-400">Alex M.</div>
                            </div>
                            <div class="grid grid-cols-12 p-2.5 bg-slate-900/40 items-center">
                                <div class="col-span-2 text-amber-400 font-semibold flex items-center gap-1.5">
                                    <span>☐</span> <span>Pending</span>
                                </div>
                                <div class="col-span-5 text-white font-medium">OAuth2 GitHub Token API Integration</div>
                                <div class="col-span-3"><span class="px-2 py-0.5 rounded bg-rose-500/10 text-rose-300 text-[10px] font-semibold border border-rose-500/20">Urgent</span></div>
                                <div class="col-span-2 text-right text-indigo-300 font-semibold">Subhadip</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Interactive V1 Classic vs V2 Developer Mode Showcase Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 border-t border-slate-800/80" id="dual-engine">
            <div class="text-center max-w-3xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-400">Interactive Architecture Showcase</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white mt-2">
                    Two Distinct Experiences. One Unified Platform.
                </h2>
                <p class="text-base sm:text-lg text-slate-400 mt-4">
                    Switch below to compare how each interface is designed for different workflow styles.
                </p>

                <!-- Interactive Mode Toggle Pill -->
                <div class="mt-8 inline-flex items-center p-1.5 rounded-2xl bg-slate-900 border border-slate-800 shadow-2xl">
                    <button type="button" id="tab-btn-v2" onclick="setLandingPreview('v2')"
                        class="px-5 py-2.5 rounded-xl font-semibold text-sm transition-all flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/20 cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Developer Mode (V2)</span>
                    </button>
                    <button type="button" id="tab-btn-v1" onclick="setLandingPreview('v1')"
                        class="px-5 py-2.5 rounded-xl font-semibold text-sm transition-all flex items-center gap-2 text-slate-400 hover:text-white cursor-pointer">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        <span>Classic Mode (V1)</span>
                    </button>
                </div>
            </div>

            <!-- Panel 1: Developer Mode (V2) Container -->
            <div id="panel-v2" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center transition-all duration-300">
                <div class="lg:col-span-5 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md text-xs font-semibold bg-indigo-500/15 text-indigo-300 border border-indigo-500/30">
                        ⚡ Inertia.js + React 18 + Tailwind
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-bold text-white">
                        Modern Engineering Cockpit
                    </h3>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                        Engineered for developers who demand fluid navigation, multi-column Kanban workflows, real-time Recharts velocity analytics, and interactive client-side agility.
                    </p>

                    <div class="space-y-3.5 text-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span class="text-slate-300"><strong>Multi-column Kanban boards</strong> with priority flags and quick subtask checklists</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span class="text-slate-300"><strong>Throughput &amp; Velocity charts</strong> tracking cycle turnaround time and weekly completion rates</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span class="text-slate-300"><strong>Multi-Tab Project Hub</strong> uniting Tasks, Markdown Notes, Credential Vault, and Discussions</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-emerald-500/10 text-emerald-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span class="text-slate-300"><strong>Collapsible shadcn sidebar</strong> with multi-company workspace switching</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-400 hover:text-indigo-300 transition-colors">
                            <span>Register with Developer Mode Default</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Developer Mode Mockup Frame -->
                <div class="lg:col-span-7">
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/90 shadow-2xl shadow-indigo-500/10 overflow-hidden">
                        <div class="px-4 py-3 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between text-xs text-slate-400">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-rose-500/80"></span>
                                <span class="w-3 h-3 rounded-full bg-amber-500/80"></span>
                                <span class="w-3 h-3 rounded-full bg-emerald-500/80"></span>
                            </div>
                            <div class="px-3 py-1 rounded-md bg-slate-900 border border-slate-800/80 font-mono text-[11px] text-slate-300">
                                workhub.io/projects/10
                            </div>
                            <span class="text-[10px] uppercase font-bold text-indigo-400">V2 Developer</span>
                        </div>
                        <div class="p-6 bg-[#0b0f19] space-y-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <div class="text-xs text-indigo-400 font-semibold uppercase tracking-wider">Project Hub • Active</div>
                                    <h4 class="text-xl font-bold text-white">E-Commerce Mobile API</h4>
                                </div>
                                <span class="px-2.5 py-1 text-xs rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 font-medium">85% Complete</span>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div class="p-3 rounded-lg bg-slate-900/80 border border-slate-800">
                                    <div class="text-[11px] text-slate-400">Tasks Completed</div>
                                    <div class="text-lg font-bold text-white font-mono mt-1">24/28</div>
                                </div>
                                <div class="p-3 rounded-lg bg-slate-900/80 border border-slate-800">
                                    <div class="text-[11px] text-slate-400">Velocity</div>
                                    <div class="text-lg font-bold text-indigo-400 font-mono mt-1">+18.4%</div>
                                </div>
                                <div class="p-3 rounded-lg bg-slate-900/80 border border-slate-800">
                                    <div class="text-[11px] text-slate-400">Credentials</div>
                                    <div class="text-lg font-bold text-purple-400 font-mono mt-1">4 Vaulted</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2">
                                    <div class="text-xs font-semibold text-slate-400 flex items-center justify-between">
                                        <span>IN PROGRESS (2)</span>
                                        <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 text-xs">
                                        <div class="font-medium text-white">Auth Token Refresh Endpoint</div>
                                        <div class="text-[10px] text-indigo-400 mt-1 flex items-center gap-1.5">
                                            <span>#BE-102</span> • <span class="text-rose-400 font-semibold">Urgent</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-950/60 border border-slate-800/80 space-y-2">
                                    <div class="text-xs font-semibold text-slate-400 flex items-center justify-between">
                                        <span>DONE (24)</span>
                                        <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    </div>
                                    <div class="p-2.5 rounded-lg bg-slate-900 border border-slate-800 text-xs opacity-80">
                                        <div class="font-medium text-slate-300 line-through">Stripe Webhook Gateway</div>
                                        <div class="text-[10px] text-emerald-400 mt-1">Resolved</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel 2: Classic Mode (V1) Container -->
            <div id="panel-v1" class="hidden grid grid-cols-1 lg:grid-cols-12 gap-8 items-center transition-all duration-300">
                <div class="lg:col-span-5 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-md text-xs font-semibold bg-blue-500/10 text-blue-300 border border-blue-500/30">
                        🏛️ Pure Laravel Blade + SSR
                    </div>
                    <h3 class="text-2xl sm:text-3xl font-bold text-white">
                        Lightweight Minimalist Workflow
                    </h3>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                        Crafted for teams that value blazing-fast server renders, zero JavaScript compilation overhead, and Notion-style inline editable tables that work effortlessly on any device.
                    </p>

                    <div class="space-y-3.5 text-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span class="text-slate-300"><strong>Notion-Style Inline Task Grid</strong>: Toggle status, change assignees, and update due dates directly from the row</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span class="text-slate-300"><strong>Zero Client Hydration Lag</strong>: Instant HTML rendering with minimal CPU and RAM usage</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span class="text-slate-300"><strong>Full Multi-Company Separation</strong>: Filter tasks by company workspaces with secure join codes</span>
                        </div>
                        <div class="flex items-start gap-3">
                            <div class="w-5 h-5 rounded-full bg-blue-500/10 text-blue-400 flex items-center justify-center shrink-0 mt-0.5">✓</div>
                            <span class="text-slate-300"><strong>One-Click PDF Export</strong>: Print or export documentation notes straight from the browser</span>
                        </div>
                    </div>

                    <div class="pt-2">
                        <a href="{{ route('register') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-blue-400 hover:text-blue-300 transition-colors">
                            <span>Register with Classic Mode Option</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                </div>

                <!-- Classic Mode Mockup Frame -->
                <div class="lg:col-span-7">
                    <div class="rounded-2xl border border-slate-800 bg-slate-900/90 shadow-2xl overflow-hidden">
                        <div class="px-4 py-3 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between text-xs text-slate-400">
                            <div class="flex items-center gap-2">
                                <span class="w-3 h-3 rounded-full bg-blue-500/80"></span>
                                <span class="w-3 h-3 rounded-full bg-slate-600"></span>
                                <span class="w-3 h-3 rounded-full bg-slate-600"></span>
                            </div>
                            <div class="px-3 py-1 rounded-md bg-slate-900 border border-slate-800/80 font-mono text-[11px] text-slate-300">
                                workhub.io/tasks
                            </div>
                            <span class="text-[10px] uppercase font-bold text-blue-400">V1 Classic</span>
                        </div>
                        <div class="p-6 bg-[#0b0f19] space-y-4">
                            <div class="flex items-center justify-between">
                                <h4 class="text-lg font-bold text-white">Tasks Spreadsheet Grid</h4>
                                <span class="text-xs px-2.5 py-1 rounded bg-blue-500/10 text-blue-300 border border-blue-500/20 font-medium">Live AJAX Autosave</span>
                            </div>

                            <!-- Table Simulation -->
                            <div class="rounded-lg border border-slate-800 overflow-hidden text-xs">
                                <div class="grid grid-cols-12 bg-slate-950/80 p-2.5 font-semibold text-slate-400 border-b border-slate-800">
                                    <div class="col-span-2">Status</div>
                                    <div class="col-span-5">Task Title</div>
                                    <div class="col-span-3">Priority</div>
                                    <div class="col-span-2 text-right">Assignee</div>
                                </div>
                                <div class="grid grid-cols-12 p-2.5 bg-slate-900/60 border-b border-slate-800/50 items-center">
                                    <div class="col-span-2 text-emerald-400 font-semibold flex items-center gap-1.5">
                                        <span>☑</span> <span>Done</span>
                                    </div>
                                    <div class="col-span-5 text-slate-400 line-through">Database Migration + Seeders</div>
                                    <div class="col-span-3"><span class="px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-300 text-[10px]">Normal</span></div>
                                    <div class="col-span-2 text-right text-slate-400">Alex M.</div>
                                </div>
                                <div class="grid grid-cols-12 p-2.5 bg-slate-900/40 items-center">
                                    <div class="col-span-2 text-amber-400 font-semibold flex items-center gap-1.5">
                                        <span>☐</span> <span>Pending</span>
                                    </div>
                                    <div class="col-span-5 text-white font-medium">Setup OAuth2 GitHub Token API</div>
                                    <div class="col-span-3"><span class="px-1.5 py-0.5 rounded bg-rose-500/20 text-rose-300 text-[10px]">Urgent</span></div>
                                    <div class="col-span-2 text-right text-indigo-300">Subhadip</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Bento Grid Architecture & Features Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 border-t border-slate-800/80" id="bento">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-400">Engineered for Performance</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white mt-2">
                    Everything You Need to Ship High-Impact Projects
                </h2>
                <p class="text-base sm:text-lg text-slate-400 mt-4">
                    Built from the ground up for modern workflows with unified multi-workspace isolation.
                </p>
            </div>

            <!-- Bento Box Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Bento Card 1: Dual-Engine Routing (Large 2-col) -->
                <div class="md:col-span-2 p-8 rounded-2xl bg-gradient-to-br from-slate-900/90 to-slate-900/40 border border-slate-800 hover:border-slate-700 transition-all shadow-xl group">
                    <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    </div>
                    <h3 class="text-xl sm:text-2xl font-bold text-white mb-3">
                        Dual-Engine Routing Architecture
                    </h3>
                    <p class="text-slate-400 text-sm sm:text-base leading-relaxed">
                        Never worry about URL fragmentation or separate databases. Our <code class="text-indigo-300 bg-indigo-950/60 px-1.5 py-0.5 rounded text-xs font-mono">ResolveUiVersion</code> middleware dynamically renders the exact view engine chosen by your account profile on pristine clean URLs (<code class="text-slate-300 text-xs font-mono">/dashboard</code>, <code class="text-slate-300 text-xs font-mono">/tasks</code>, <code class="text-slate-300 text-xs font-mono">/routes</code>).
                    </p>
                </div>

                <!-- Bento Card 2: Multi-Company Workspaces -->
                <div class="p-8 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-slate-700 transition-all shadow-xl">
                    <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/20 text-purple-400 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Multi-Company Spaces</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Isolate projects between client contracts and internal tools. Invite team members with unique 6-character access keys.
                    </p>
                </div>

                <!-- Bento Card 3: Velocity & Analytics Engine -->
                <div class="p-8 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-slate-700 transition-all shadow-xl">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Velocity Engine</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Track throughput, project turnaround times, and burndown velocity with dynamic Recharts area charts and completion ratios.
                    </p>
                </div>

                <!-- Bento Card 4: Credential Vault & REST APIs -->
                <div class="p-8 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-slate-700 transition-all shadow-xl">
                    <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Credential Vault</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Safeguard server IPs, database secrets, and API keys per project with eye-toggle masks and rate-limited REST webhooks.
                    </p>
                </div>

                <!-- Bento Card 5: Soft-Delete Trash Bin & Audit -->
                <div class="p-8 rounded-2xl bg-slate-900/60 border border-slate-800 hover:border-slate-700 transition-all shadow-xl">
                    <div class="w-12 h-12 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center mb-6">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </div>
                    <h3 class="text-xl font-bold text-white mb-2">Trash Recovery &amp; Audit</h3>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Granular Spatie activity logs track every task change. Soft-deleted items are safely recoverable with 30-day auto-pruning.
                    </p>
                </div>
            </div>
        </section>

        <!-- Workflow Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 border-t border-slate-800/80" id="workflow">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-xs font-bold uppercase tracking-widest text-indigo-400">Streamlined Workflow</span>
                <h2 class="text-3xl sm:text-5xl font-extrabold text-white mt-2">
                    How Modern Teams Execute on WorkHub
                </h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-8 rounded-2xl bg-slate-900/40 border border-slate-800 relative">
                    <div class="w-10 h-10 rounded-full bg-indigo-500/10 text-indigo-400 border border-indigo-500/30 font-bold text-sm flex items-center justify-center mb-4">1</div>
                    <h4 class="text-lg font-bold text-white mb-2">Set Up Your Organization</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Create personal or company workspaces, configure permissions, and invite members with zero friction.
                    </p>
                </div>
                <div class="p-8 rounded-2xl bg-slate-900/40 border border-slate-800 relative">
                    <div class="w-10 h-10 rounded-full bg-purple-500/10 text-purple-400 border border-purple-500/30 font-bold text-sm flex items-center justify-center mb-4">2</div>
                    <h4 class="text-lg font-bold text-white mb-2">Choose Your Interface Mode</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Select Developer Mode (V2) or Classic Mode (V1) based on your daily work habits. Switch anytime with zero data loss.
                    </p>
                </div>
                <div class="p-8 rounded-2xl bg-slate-900/40 border border-slate-800 relative">
                    <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 font-bold text-sm flex items-center justify-center mb-4">3</div>
                    <h4 class="text-lg font-bold text-white mb-2">Execute &amp; Ship</h4>
                    <p class="text-slate-400 text-sm leading-relaxed">
                        Manage tasks with Kanban or inline spreadsheets, draft documentation notes, vault keys, and ship features faster.
                    </p>
                </div>
            </div>
        </section>

        <!-- CTA Callout Banner -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="rounded-3xl p-8 sm:p-14 bg-gradient-to-r from-indigo-900/90 via-purple-900/80 to-slate-900 border border-indigo-500/30 text-center relative overflow-hidden shadow-2xl">
                <div class="relative z-10 max-w-3xl mx-auto space-y-6">
                    <h2 class="text-3xl sm:text-5xl font-extrabold text-white tracking-tight">
                        Ready to elevate your engineering workspace?
                    </h2>
                    <p class="text-slate-300 text-base sm:text-lg leading-relaxed">
                        Create your free account today and choose between Developer Mode and Classic Mode in seconds.
                    </p>
                    <div class="pt-4 flex flex-wrap justify-center gap-4">
                        @auth
                            <a href="{{ route('dashboard') }}" class="px-8 py-3.5 rounded-xl bg-white text-slate-950 font-bold text-base hover:bg-slate-100 transition-all shadow-xl">
                                Go to Dashboard
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="px-8 py-3.5 rounded-xl bg-white text-slate-950 font-bold text-base hover:bg-slate-100 transition-all shadow-xl transform hover:-translate-y-0.5">
                                Join WorkHub Now
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-800/80 bg-slate-950 py-12 text-slate-400 text-xs sm:text-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" class="w-6 h-6">
                    <defs>
                        <linearGradient id="footer-logo-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" stop-color="#6366f1" />
                            <stop offset="100%" stop-color="#a855f7" />
                        </linearGradient>
                    </defs>
                    <path d="M16 2 L28 9 L28 23 L16 30 L4 23 L4 9 Z" fill="none" stroke="url(#footer-logo-grad)" stroke-width="2.5" stroke-linejoin="round" />
                    <path d="M16 8 L23 12 L23 20 L16 24 L9 20 L9 12 Z" fill="url(#footer-logo-grad)" opacity="0.9" />
                    <circle cx="16" cy="16" r="3.5" fill="#ffffff" />
                </svg>
                <span class="font-bold text-white text-base">WorkHub</span>
                <span class="text-slate-600">|</span>
                <span>Dual-Engine Workspace Architecture</span>
            </div>

            <div class="flex items-center gap-6">
                <a href="#dual-engine" class="hover:text-white transition-colors">Modes</a>
                <a href="#bento" class="hover:text-white transition-colors">Features</a>
                <a href="{{ route('routes.index') }}" class="hover:text-white transition-colors">Routes</a>
                <a href="{{ route('login') }}" class="hover:text-white transition-colors">Sign In</a>
                <a href="{{ route('register') }}" class="hover:text-white transition-colors">Register</a>
            </div>

            <div>
                &copy; {{ date('Y') }} WorkHub. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Interactive Mode Preview Switcher & Theme Handler -->
    <script>
        function toggleLandingTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            document.documentElement.classList.toggle('light', !isDark);
            localStorage.setItem('workhub_theme', isDark ? 'dark' : 'light');
            updateLandingThemeIcon(isDark);
        }

        function updateLandingThemeIcon(isDark) {
            const sun = document.getElementById('themeSunIcon');
            const moon = document.getElementById('themeMoonIcon');
            if (sun && moon) {
                sun.classList.toggle('hidden', !isDark);
                moon.classList.toggle('hidden', isDark);
            }
        }

        function setLandingPreview(mode) {
            const isV1 = (mode === 'v1');

            // Hero Buttons
            const heroV2 = document.getElementById('hero-btn-v2');
            const heroV1 = document.getElementById('hero-btn-v1');
            if (heroV2 && heroV1) {
                if (isV1) {
                    heroV1.className = 'px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 transition-all cursor-pointer bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/25';
                    heroV2.className = 'px-5 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-400 hover:text-white flex items-center gap-2 transition-all cursor-pointer';
                } else {
                    heroV2.className = 'px-5 py-2.5 rounded-xl font-bold text-xs sm:text-sm flex items-center gap-2 transition-all cursor-pointer bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/25';
                    heroV1.className = 'px-5 py-2.5 rounded-xl font-medium text-xs sm:text-sm text-slate-400 hover:text-white flex items-center gap-2 transition-all cursor-pointer';
                }
            }

            // Hero Mockups
            const heroMockV2 = document.getElementById('hero-mockup-v2');
            const heroMockV1 = document.getElementById('hero-mockup-v1');
            if (heroMockV2 && heroMockV1) {
                heroMockV2.classList.toggle('hidden', isV1);
                heroMockV1.classList.toggle('hidden', !isV1);
            }

            // Showcase Section Panels & Tabs
            const panelV2 = document.getElementById('panel-v2');
            const panelV1 = document.getElementById('panel-v1');
            const tabV2 = document.getElementById('tab-btn-v2');
            const tabV1 = document.getElementById('tab-btn-v1');

            if (panelV2 && panelV1) {
                panelV2.classList.toggle('hidden', isV1);
                panelV1.classList.toggle('hidden', !isV1);
            }
            if (tabV2 && tabV1) {
                if (isV1) {
                    tabV1.className = 'px-5 py-2.5 rounded-xl font-semibold text-sm transition-all flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-lg shadow-blue-500/20 cursor-pointer';
                    tabV2.className = 'px-5 py-2.5 rounded-xl font-semibold text-sm transition-all flex items-center gap-2 text-slate-400 hover:text-white cursor-pointer';
                } else {
                    tabV2.className = 'px-5 py-2.5 rounded-xl font-semibold text-sm transition-all flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-500/20 cursor-pointer';
                    tabV1.className = 'px-5 py-2.5 rounded-xl font-semibold text-sm transition-all flex items-center gap-2 text-slate-400 hover:text-white cursor-pointer';
                }
            }
        }

        // Initialize state on DOMContentLoaded
        document.addEventListener('DOMContentLoaded', function() {
            const currentTheme = localStorage.getItem('workhub_theme') || 'dark';
            updateLandingThemeIcon(currentTheme === 'dark');
        });
    </script>
</body>
</html>
