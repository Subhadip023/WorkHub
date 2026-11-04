<x-guest-layout>
    <div class="bg-slate-900/70 backdrop-blur-xl border border-slate-800/80 shadow-2xl rounded-2xl p-6 sm:p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-white">Create Your Account</h1>
            <p class="text-sm text-slate-400 mt-1">Start collaborating with WorkHub's dual-engine workspace</p>
        </div>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <!-- 2-Mode Experience Selector Card -->
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">
                    Choose Your Workspace Mode
                </label>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="mode-selector-grid">
                    <!-- Developer Mode (V2) Card -->
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 select-none border-indigo-500 bg-indigo-500/10 shadow-lg shadow-indigo-500/10" id="card-mode-v2">
                        <input type="radio" name="ui_version" value="v2" class="sr-only" checked onchange="updateModeSelection('v2')">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-indigo-400 uppercase tracking-wide">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Developer (V2)
                            </span>
                            <span class="px-1.5 py-0.5 text-[10px] font-semibold bg-indigo-500/20 text-indigo-300 rounded border border-indigo-500/30">Default</span>
                        </div>
                        <h4 class="text-sm font-semibold text-white">React SPA Cockpit</h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                            Dark mode, interactive Kanban boards, project velocity charts & instant navigation.
                        </p>
                        <div class="mt-3 flex items-center gap-1.5 text-[11px] text-indigo-300 font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                            <span>Modern & High-Velocity</span>
                        </div>
                    </label>

                    <!-- Classic Mode (V1) Card -->
                    <label class="relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 select-none border-slate-800 bg-slate-950/40 hover:border-slate-700" id="card-mode-v1">
                        <input type="radio" name="ui_version" value="v1" class="sr-only" onchange="updateModeSelection('v1')">
                        <div class="flex items-center justify-between mb-2">
                            <span class="inline-flex items-center gap-1.5 text-xs font-bold text-slate-300 uppercase tracking-wide">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                Classic (V1)
                            </span>
                        </div>
                        <h4 class="text-sm font-semibold text-white">Blade Minimalist</h4>
                        <p class="text-xs text-slate-400 mt-1 leading-relaxed">
                            Lightweight server-rendered views with Notion-style inline task spreadsheet table.
                        </p>
                        <div class="mt-3 flex items-center gap-1.5 text-[11px] text-slate-400 font-medium">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-500"></span>
                            <span>Low Memory & Clean SSR</span>
                        </div>
                    </label>
                </div>
                <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Both modes share the same projects and database. Switch anytime in Profile Settings.
                </p>
                @error('ui_version')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Full Name -->
            <div>
                <label for="name" class="block text-xs font-medium text-slate-300 mb-1.5">Full Name</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                    placeholder="Subhadip Chakraborty"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                @error('name')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email', request('email')) }}" required autocomplete="username"
                    placeholder="developer@workhub.io"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                @error('email')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Fields -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs font-medium text-slate-300 mb-1.5">Password</label>
                    <input id="password" type="password" name="password" required autocomplete="new-password"
                        placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-lg bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    @error('password')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs font-medium text-slate-300 mb-1.5">Confirm Password</label>
                    <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                        placeholder="••••••••"
                        class="w-full px-3.5 py-2.5 rounded-lg bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                    @error('password_confirmation')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3 px-4 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-semibold text-sm shadow-lg shadow-indigo-500/25 transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0 cursor-pointer flex items-center justify-center gap-2">
                <span>Create Account & Launch Workspace</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-800/80 text-center">
            <p class="text-xs text-slate-400">
                Already have an account?
                <a href="{{ route('login') }}" class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors ml-1">
                    Sign in here
                </a>
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
        function updateModeSelection(mode) {
            const cardV2 = document.getElementById('card-mode-v2');
            const cardV1 = document.getElementById('card-mode-v1');
            if (mode === 'v2') {
                cardV2.className = 'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 select-none border-indigo-500 bg-indigo-500/10 shadow-lg shadow-indigo-500/10';
                cardV1.className = 'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 select-none border-slate-800 bg-slate-950/40 hover:border-slate-700';
            } else {
                cardV1.className = 'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 select-none border-indigo-500 bg-indigo-500/10 shadow-lg shadow-indigo-500/10';
                cardV2.className = 'relative flex flex-col p-4 rounded-xl border-2 cursor-pointer transition-all duration-200 select-none border-slate-800 bg-slate-950/40 hover:border-slate-700';
            }
        }
    </script>
    @endpush
</x-guest-layout>
