<div class="d-flex justify-content-between align-items-center mb-4 border-bottom position-relative">
    <ul class="nav nav-tabs border-bottom-0" id="projectShowTabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('projects.show') ? 'active' : '' }} font-weight-bold" href="{{ route('projects.show', $project) }}">
                <i class="fas fa-tasks mr-2"></i>Tasks
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('projects.notes') ? 'active' : '' }} font-weight-bold" href="{{ route('projects.notes', $project) }}">
                <i class="fas fa-sticky-note mr-2"></i>Notes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('projects.credentials') ? 'active' : '' }} font-weight-bold" href="{{ route('projects.credentials', $project) }}">
                <i class="fas fa-key mr-2"></i>Credentials
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->routeIs('projects.external-api') ? 'active' : '' }} font-weight-bold" href="{{ route('projects.external-api', $project) }}">
                <i class="fas fa-plug mr-2"></i>External API
            </a>
        </li>
    </ul>

    <div class="pb-2">
        <button type="button" class="btn btn-sm btn-outline-primary shadow-sm font-weight-bold" id="btnToggleDiscussion">
            <i class="fas fa-comments mr-1"></i> Discussion
            <span class="badge badge-primary font-weight-bold ml-1">{{ count($comments ?? $project->comments ?? []) }}</span>
        </button>
    </div>
</div>
