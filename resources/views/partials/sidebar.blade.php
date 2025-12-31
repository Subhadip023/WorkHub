<ul class="navbar-nav bg-dark sidebar sidebar-dark">

    @auth
        <li class="nav-item">
            <a class="nav-link" href="{{ route('projects.create') }}">
                Create Project
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="{{ route('projects.index') }}">
                Projects
            </a>
        </li>

        <li class="nav-item">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="btn btn-link nav-link text-white">Logout</button>
            </form>
        </li>
    @endauth

</ul>
