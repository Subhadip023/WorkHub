@props([
    'taskType',
    'editable' => false,
    'wrapper' => 'td',
    'name' => null,
    'taskId' => null,
    'field' => 'type',
])

@php
    $typeOptions = [
        1 => 'Task',
        2 => 'Bug',
        3 => 'Feature',
        4 => 'Improvement',
    ];

    $typeMeta = [
        1 => ['badge' => 'badge-light border text-gray-800', 'icon' => 'fas fa-tasks text-secondary'],
        2 => ['badge' => 'badge-danger', 'icon' => 'fas fa-bug'],
        3 => ['badge' => 'badge-success', 'icon' => 'fas fa-rocket'],
        4 => ['badge' => 'badge-info', 'icon' => 'fas fa-chart-line'],
    ];

    $currentMeta = $typeMeta[$taskType] ?? $typeMeta[1];
@endphp

<x-notion-select
    :current="$taskType"
    :options="$typeOptions"
    :option-meta="$typeMeta"
    :current-badge="$currentMeta['badge']"
    :current-icon="$currentMeta['icon']"
    :editable="$editable"
    :wrapper="$wrapper"
    :name="$name"
    :task-id="$taskId"
    :field="$field"
/>
