@props([
    'title' => 'Edit',
    'label' => null,
    'icon' => 'edit',
    'href' => null
])

@if($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => 'btn btn-sm btn-light border text-primary shadow-xs']) }} title="{{ $title }}">
        <i class="fas fa-{{ $icon }} {{ $label ? 'mr-1' : '' }}"></i>@if($label){{ $label }}@endif
    </a>
@else
    <button type="button" {{ $attributes->merge(['class' => 'btn btn-sm btn-light border text-primary shadow-xs']) }} title="{{ $title }}">
        <i class="fas fa-{{ $icon }} {{ $label ? 'mr-1' : '' }}"></i>@if($label){{ $label }}@endif
    </button>
@endif
