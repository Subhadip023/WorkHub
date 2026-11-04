<x-guest-layout>
    <div class="bg-slate-900/70 backdrop-blur-xl border border-slate-800/80 shadow-2xl rounded-2xl p-6 sm:p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold tracking-tight text-white">Welcome Back</h1>
            <p class="text-sm text-slate-400 mt-1">Sign in to access your projects and tasks in your preferred mode</p>
        </div>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4 text-emerald-400 text-sm font-medium" :status="session('status')" />

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-xs font-medium text-slate-300 mb-1.5">Email Address</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    placeholder="developer@workhub.io"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                @error('email')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-medium text-slate-300">Password</label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-xs text-indigo-400 hover:text-indigo-300 transition-colors">
                            Forgot password?
                        </a>
                    @endif
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full px-3.5 py-2.5 rounded-lg bg-slate-950/60 border border-slate-800 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition-all">
                @error('password')
                    <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center gap-2 cursor-pointer select-none">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="rounded bg-slate-950 border-slate-800 text-indigo-500 focus:ring-indigo-500 focus:ring-offset-slate-900 w-4 h-4 cursor-pointer">
                    <span class="text-xs text-slate-300 font-medium">Remember my session</span>
                </label>
            </div>

            <!-- Optional Google reCAPTCHA -->
            @if(config('services.recaptcha.site_key') && !config('services.recaptcha.skip'))
                <div class="flex flex-col items-center justify-center my-3">
                    <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                    @error('g-recaptcha-response')
                        <p class="text-xs text-rose-400 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <!-- Submit Button -->
            <button type="submit" class="w-full py-3 px-4 rounded-lg bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-semibold text-sm shadow-lg shadow-indigo-500/25 transition-all duration-200 transform hover:-translate-y-0.5 active:translate-y-0 cursor-pointer flex items-center justify-center gap-2">
                <span>Sign In to Workspace</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </button>
        </form>

        <div class="mt-6 pt-5 border-t border-slate-800/80 text-center">
            <p class="text-xs text-slate-400">
                Don't have an account yet?
                <a href="{{ route('register') }}" class="font-medium text-indigo-400 hover:text-indigo-300 transition-colors ml-1">
                    Create an account
                </a>
            </p>
        </div>
    </div>

    @if(config('services.recaptcha.site_key') && !config('services.recaptcha.skip'))
        @push('scripts')
            <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        @endpush
    @endif
</x-guest-layout>
