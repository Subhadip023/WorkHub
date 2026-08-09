@props([
    'title' => 'Copy Task',
    'label' => null,
    'icon' => 'copy',
    'type' => 'submit'
])

<button type="{{ $type }}" {{ $attributes->merge(['class' => 'btn btn-sm btn-light border text-info shadow-xs']) }} title="{{ $title }}">
    <i class="fas fa-{{ $icon }} {{ $label ? 'mr-1' : '' }}"></i>@if($label){{ $label }}@endif
</button>
