<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ url('/') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-briefcase"></i>
        </div>
        <div class="sidebar-brand-text mx-3">WorkHub</div>
    </a>

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

   

</ul>

