@props([
    'status',
    'editable' => false,
    'wrapper' => 'td',
    'name' => null,
    'taskId' => null,
    'field' => 'status',
])

@php
    $statusOptions = [
        1 => 'To Do',
        2 => 'In Progress',
        3 => 'Completed',
        4 => 'On Hold',
    ];

    $statusMeta = [
        1 => ['badge' => 'badge-secondary', 'icon' => 'fas fa-list'],
        2 => ['badge' => 'badge-warning text-dark', 'icon' => 'fas fa-spinner'],
        3 => ['badge' => 'badge-success', 'icon' => 'fas fa-check'],
        4 => ['badge' => 'badge-danger', 'icon' => 'fas fa-pause'],
    ];

    $currentMeta = $statusMeta[$status] ?? $statusMeta[1];
@endphp

<x-notion-select
    :current="$status"
    :options="$statusOptions"
    :option-meta="$statusMeta"
    :current-badge="$currentMeta['badge']"
    :current-icon="$currentMeta['icon']"
    :editable="$editable"
    :wrapper="$wrapper"
    :name="$name"
    :task-id="$taskId"
    :field="$field"
/>
