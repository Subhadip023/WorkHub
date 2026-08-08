@props([
    'current'           => null,             // Currently selected value
    'options'           => [],               // Simple [value => label] map
    'optionMeta'        => [],               // Per-option [value => ['badge' => ..., 'icon' => ..., 'background' => ...]]
    'currentBadge'      => 'badge-secondary',// Badge class for current value
    'currentIcon'       => null,             // Optional icon class for current value
    'currentAvatar'     => null,             // Optional avatar URL for current value
    'currentInitials'   => null,             // Avatar fallback initials for current value
    'currentBackground' => null,             // Optional inline background color for current badge
    'editable'          => false,            // Enables interactive dropdown toggle
    'onChange'          => null,             // Custom JS callback name or expression on change
    'name'              => null,             // Hidden input name for form usage
    'id'                => null,             // Element ID wrapper/display override
    'class'             => null,             // Extra CSS classes for display wrapper
    'label'             => null,             // Optional label text above badge
    'wrapper'           => 'td',             // 'td' | 'div' | 'none'
])

@php
    $currentLabel = $options[(string) $current] ?? '—';
    $elementId = $id;
    $extraClasses = trim($class ?? '');

    $wrapClass = 'notion-select-cell';
    if ($wrapper === 'td') {
        $wrapClass .= ' align-middle d-none d-md-table-cell';
    }
@endphp

@if($wrapper !== 'none')
<{{ $wrapper }} class="{{ $wrapClass }}" style="position: relative;">
@endif

    @if($label)
        <span class="font-weight-bold text-xs text-uppercase d-block mb-1 text-gray-700">{{ $label }}</span>
    @endif

    @if($name)
        <input type="hidden" name="{{ $name }}" value="{{ $current }}" class="notion-select-input" @if($elementId) id="{{ $elementId }}_input" @endif>
    @endif

    @if($editable)
    <span class="notion-select-display {{ $extraClasses }}"
          @if($elementId) id="{{ $elementId }}" @endif
          @if($onChange) data-onchange="{{ $onChange }}" @endif
          style="cursor: pointer; user-select: none;"
          title="Click to change">
    @endif

    {{-- Current value badge --}}
    <span class="badge {{ $currentBadge }} px-2 py-1 shadow-sm font-weight-bold notion-current-badge" @if($currentBackground) style="background-color: {{ $currentBackground }};" @endif>
        @if($currentAvatar)
            <img src="{{ $currentAvatar }}" alt="" class="notion-select-avatar mr-1">
        @elseif($currentInitials)
            <span class="notion-select-avatar notion-select-avatar-initials mr-1">{{ $currentInitials }}</span>
        @elseif($currentIcon)
            <i class="{{ $currentIcon }} mr-1"></i>
        @endif
        {{ $currentLabel }}
    </span>

    @if($editable)
    </span>

    {{-- Notion dropdown options --}}
    <div class="notion-select-dropdown" style="display: none;">
        @foreach($options as $optValue => $optLabel)
            @php
                $meta = $optionMeta[$optValue] ?? [];
                $optionBadge = $meta['badge'] ?? 'badge-secondary';
                $optionIcon = $meta['icon'] ?? null;
                $optionAvatar = $meta['avatar'] ?? null;
                $optionInitials = $meta['initials'] ?? null;
                $optionBackground = $meta['background'] ?? null;
            @endphp
            <div class="notion-select-option"
                 data-value="{{ $optValue }}"
                 data-label="{{ $optLabel }}"
                 data-badge="{{ $optionBadge }}"
                 data-icon="{{ $optionIcon }}"
                 data-avatar="{{ $optionAvatar }}"
                 data-initials="{{ $optionInitials }}"
                 data-background="{{ $optionBackground }}"
                 @if($optionBackground) style="background-color: {{ $optionBackground }};" @endif>
                <span class="badge {{ $optionBadge }} px-2 py-1 shadow-sm font-weight-bold" @if($optionBackground) style="background-color: {{ $optionBackground }};" @endif>
                    @if($optionAvatar)
                        <img src="{{ $optionAvatar }}" alt="" class="notion-select-avatar mr-1">
                    @elseif($optionInitials)
                        <span class="notion-select-avatar notion-select-avatar-initials mr-1">{{ $optionInitials }}</span>
                    @elseif($optionIcon)
                        <i class="{{ $optionIcon }} mr-1"></i>
                    @endif
                    {{ $optLabel }}
                </span>
            </div>
        @endforeach
    </div>
    @endif

@if($wrapper !== 'none')
</{{ $wrapper }}>
@endif
