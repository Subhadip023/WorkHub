@props([
    'title' => 'Delete',
    'label' => null,
    'icon' => 'trash',
    'type' => 'button'
])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn btn-sm btn-light border text-danger shadow-xs']) }} title="{{ $title }}">
    <i class="fas fa-{{ $icon }} {{ $label ? 'mr-1' : '' }}"></i>@if($label){{ $label }}@endif
</button>
