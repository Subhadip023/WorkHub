@props([
    'variant' => 'primary',
    'title' => '',
    'label' => null,
    'icon' => null
])

<button {{ $attributes->merge(['class' => 'btn btn-sm btn-light border text-' . $variant . ' shadow-xs']) }} title="{{ $title }}">
    @if($icon)
        <i class="fas fa-{{ $icon }} {{ $label ? 'mr-1' : '' }}"></i>
    @endif
    @if($label){{ $label }}@endif
</button>
