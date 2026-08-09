@props([
    'title' => 'Move Task',
    'label' => null,
    'icon' => 'exchange-alt',
    'type' => 'button'
])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn btn-sm btn-light border text-primary shadow-xs']) }} title="{{ $title }}">
    <i class="fas fa-{{ $icon }} {{ $label ? 'mr-1' : '' }}"></i>@if($label){{ $label }}@endif
</button>
