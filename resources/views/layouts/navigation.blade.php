<nav class="bg-white border-b border-gray-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('tasks.index') }}">
                        <span class="font-bold text-lg text-indigo-600">
                            Matainja Technology
                        </span>
                    </a>
                </div>

                <!-- Navigation -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    <a href="{{ route('tasks.index') }}"
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium
                              {{ request()->routeIs('tasks.*') ? 'border-indigo-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Tasks
                    </a>
                </div>
            </div>

            <!-- Right side -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-sm text-red-600 hover:underline">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
