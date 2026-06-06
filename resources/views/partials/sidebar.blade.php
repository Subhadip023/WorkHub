<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('dashboard') }}">
        <div class="sidebar-brand-icon mr-2">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" style="width: 28px; height: 28px; filter: drop-shadow(0 2px 4px rgba(255, 255, 255, 0.2));">
                <defs>
                    <linearGradient id="sidebar-logo-grad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#ffffff" />
                        <stop offset="100%" stop-color="#cbd5e1" />
                    </linearGradient>
                </defs>
                <path d="M16 2 L28 9 L28 23 L16 30 L4 23 L4 9 Z" fill="none" stroke="url(#sidebar-logo-grad)" stroke-width="2.5" stroke-linejoin="round" />
                <path d="M16 8 L23 12 L23 20 L16 24 L9 20 L9 12 Z" fill="url(#sidebar-logo-grad)" opacity="0.9" />
                <circle cx="16" cy="16" r="3.5" fill="#4e73df" />
            </svg>
        </div>
        <div class="sidebar-brand-text">WorkHub</div>
    </a>

    <!-- Mobile Close Button (only visible on mobile/tablet) -->
    <button class="btn btn-link text-white d-md-none" id="sidebarCloseMobile" style="position: absolute; right: 15px; top: 15px; font-size: 1.25rem; z-index: 1060; opacity: 0.7; transition: opacity 0.2s; border: none; outline: none; box-shadow: none;">
        <i class="fas fa-times"></i>
    </button>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{Route::currentRouteName() == 'dashboard' ? 'active' : ''}}">
        <a class="nav-link" href="{{ route('dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>
    <li class="nav-item {{Route::currentRouteName() == 'projects.index' ? 'active' : ''}}">
        <a class="nav-link" href="{{ route('projects.index') }}">
    <i class="fas fa-project-diagram"></i>
            <span>Projects</span>
        </a>
    </li>
    <li class="nav-item {{Route::currentRouteName() == 'tasks.index' ? 'active' : ''}}">
        <a class="nav-link" href="{{ route('tasks.index') }}">
            <i class="fas fa-tasks"></i>
            <span>Tasks</span>
        </a>
    </li>
    <li class="nav-item {{Route::currentRouteName() == 'notes.index' ? 'active' : ''}}">
        <a class="nav-link" href="{{ route('notes.index') }}">
            <i class="fas fa-sticky-note"></i>
            <span>Notes</span>
        </a>
    </li>
    <li class="nav-item {{request()->routeIs('companies.*') ? 'active' : ''}}">
        <a class="nav-link" href="{{ route('companies.index') }}">
            <i class="fas fa-sitemap"></i>
            <span>Organizations</span>
        </a>
    </li>

   

</ul>

