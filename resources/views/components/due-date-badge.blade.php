@props([
    'dueDate' => null,
    'status' => null,
    'editable' => false,
    'wrapper' => 'td',
    'name' => null,
    'taskId' => null,
])

@php
    $today = \Carbon\Carbon::today();
    $date = $dueDate ? \Carbon\Carbon::parse($dueDate)->startOfDay() : null;

    if (! $date) {
        $badgeClass = 'badge-light border text-muted';
        $icon = 'far fa-calendar-plus';
        $label = 'No due date';
    } elseif ($date->lt($today) && $status != 3) {
        $badgeClass = 'badge-danger';
        $icon = 'fas fa-exclamation-triangle';
        $label = 'Overdue (' . $date->format('M d') . ')';
    } elseif ($date->isSameDay($today)) {
        $badgeClass = 'badge-warning text-dark';
        $icon = 'fas fa-clock';
        $label = 'Due Today';
    } else {
        $badgeClass = 'badge-light border text-gray-800';
        $icon = 'far fa-calendar-alt text-primary';
        $label = $date->format('M d');
    }
@endphp

@if($wrapper !== 'none')
<{{ $wrapper }} class="notion-date-cell{{ $wrapper === 'td' ? ' align-middle d-none d-md-table-cell' : '' }}" style="position: relative;" @if($taskId) data-task-id="{{ $taskId }}" @endif>
@endif

    @if($name)
        <input type="hidden" name="{{ $name }}" value="{{ $date?->format('Y-m-d') }}" class="notion-date-hidden-input">
    @endif

    @if($editable)
    <span class="notion-date-display" style="cursor: pointer; user-select: none;" title="Click to change due date">
    @endif
        <span class="badge {{ $badgeClass }} px-2 py-1 shadow-sm font-weight-bold notion-date-badge">
            <i class="{{ $icon }} mr-1"></i>{{ $label }}
        </span>
    @if($editable)
    </span>

    <div class="notion-date-dropdown" style="display: none;">
        <input type="date" class="form-control form-control-sm notion-date-input" value="{{ $date?->format('Y-m-d') }}">
        <button type="button" class="btn btn-sm btn-link text-danger p-0 mt-2 notion-date-clear">Clear due date</button>
    </div>
    @endif

@if($wrapper !== 'none')
</{{ $wrapper }}>
@endif
