@include('partials.tasks_table', [
    'tasks' => $tasks,
    'companyUsers' => $companyUsers ?? collect(),
    'project' => $project ?? null,
    'showProjectColumn' => false,
    'showFilters' => $showFilters ?? false,
    'wrapInCard' => $wrapInCard ?? true,
    'cardTitle' => $cardTitle ?? 'Project Tasks'
])
