@props([
    'priority',
    'editable' => false,
    'wrapper'  => 'td',
    'name'     => null,
])

{{--
    Priority Badge — thin wrapper around x-notion-select.
    Pre-configures the options, badge class, and icon for priority.

    Usage (table):      <x-priority-badge :priority="$task->priority" :editable="..." />
    Usage (standalone): <x-priority-badge :priority="$task->priority" :editable="..." wrapper="div" />
--}}

@php
    $priorityOptions = [
        4 => 'Urgent',
        3 => 'High',
        2 => 'Medium',
        1 => 'Low',
    ];

    $priorityBadges = [
        1 => 'badge-secondary',
        2 => 'badge-info',
        3 => 'badge-warning text-dark',
        4 => 'badge-danger',
    ];

    $priorityIcons = [
        1 => 'fas fa-arrow-down',
        2 => 'fas fa-minus',
        3 => 'fas fa-arrow-up',
        4 => 'fas fa-fire',
    ];

    $priorityOptionMeta = [];
    foreach ($priorityOptions as $value => $label) {
        $priorityOptionMeta[$value] = [
            'badge' => $priorityBadges[$value],
            'icon' => $priorityIcons[$value],
        ];
    }

    $currentBadge = $priorityBadges[$priority] ?? 'badge-secondary';
    $currentIcon  = $priorityIcons[$priority]  ?? null;
@endphp

<x-notion-select
    :current="$priority"
    :options="$priorityOptions"
    :option-meta="$priorityOptionMeta"
    :current-badge="$currentBadge"
    :current-icon="$currentIcon"
    :editable="$editable"
    :wrapper="$wrapper"
    :name="$name"
/>
