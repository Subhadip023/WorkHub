@props([
    'assignedTo' => null,
    'assignedUser' => null,
    'users' => [],
    'editable' => false,
    'wrapper' => 'td',
    'name' => null,
    'taskId' => null,
    'field' => 'assigned_to',
])

@php
    $assigneeOptions = ['' => 'Unassigned'];
    $assigneeMeta = [
        '' => ['badge' => 'badge-light border text-muted', 'icon' => 'fas fa-user-slash'],
    ];

    foreach ($users as $user) {
        $assigneeOptions[$user->id] = $user->name;
        $assigneeMeta[$user->id] = [
            'badge' => 'badge-light border text-gray-800',
            'avatar' => $user->profile_image ? asset('storage/' . $user->profile_image) : null,
            'initials' => strtoupper(substr($user->name, 0, 1)),
        ];
    }

    if ($assignedUser && ! isset($assigneeMeta[$assignedUser->id])) {
        $assigneeOptions[$assignedUser->id] = $assignedUser->name;
        $assigneeMeta[$assignedUser->id] = [
            'badge' => 'badge-light border text-gray-800',
            'avatar' => $assignedUser->profile_image ? asset('storage/' . $assignedUser->profile_image) : null,
            'initials' => strtoupper(substr($assignedUser->name, 0, 1)),
        ];
    }

    $currentMeta = $assigneeMeta[(string) $assignedTo] ?? $assigneeMeta[''];
@endphp

<x-notion-select
    :current="$assignedTo"
    :options="$assigneeOptions"
    :option-meta="$assigneeMeta"
    :current-badge="$currentMeta['badge']"
    :current-icon="$currentMeta['icon'] ?? null"
    :current-avatar="$currentMeta['avatar'] ?? null"
    :current-initials="$currentMeta['initials'] ?? null"
    :editable="$editable"
    :wrapper="$wrapper"
    :name="$name"
    :task-id="$taskId"
    :field="$field"
/>
