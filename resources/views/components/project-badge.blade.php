@props([
    'projectId' => null,
    'project' => null,
    'projects' => [],
    'editable' => false,
    'wrapper' => 'td',
    'name' => null,
    'taskId' => null,
    'field' => 'project_id',
])

@php
    $projectOptions = ['' => 'Personal'];
    $projectMeta = [
        '' => ['badge' => 'badge-light border text-muted', 'icon' => 'fas fa-user-lock'],
    ];

    foreach ($projects as $projectItem) {
        $projectOptions[$projectItem->id] = $projectItem->name;
        $projectMeta[$projectItem->id] = [
            'badge' => 'text-white',
            'icon' => 'fas fa-folder',
            'background' => $projectItem->theme ?: '#4e73df',
        ];
    }

    if ($project && ! isset($projectMeta[$project->id])) {
        $projectOptions[$project->id] = $project->name;
        $projectMeta[$project->id] = [
            'badge' => 'text-white',
            'icon' => 'fas fa-folder',
            'background' => $project->theme ?: '#4e73df',
        ];
    }

    $currentMeta = $projectMeta[(string) $projectId] ?? $projectMeta[''];
@endphp

<x-notion-select
    :current="$projectId"
    :options="$projectOptions"
    :option-meta="$projectMeta"
    :current-badge="$currentMeta['badge']"
    :current-icon="$currentMeta['icon'] ?? null"
    :current-background="$currentMeta['background'] ?? null"
    :editable="$editable"
    :wrapper="$wrapper"
    :name="$name"
    :task-id="$taskId"
    :field="$field"
/>
