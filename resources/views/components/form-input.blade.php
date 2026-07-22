@props(['name', 'label', 'type' => 'text', 'value' => null, 'required' => false, 'bag' => 'default'])
@php $bagErrors = $errors->getBag($bag); @endphp
<div class="mb-3">
    <label for="{{ $name }}" class="form-label">{{ $label }}</label>
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ $type === 'password' ? '' : old($name, $value) }}"
        {{ $required ? 'required' : '' }}
        {{ $attributes->merge(['class' => 'form-control'.($bagErrors->has($name) ? ' is-invalid' : '')]) }}
    >
    @if ($bagErrors->has($name))
        <div class="invalid-feedback">{{ $bagErrors->first($name) }}</div>
    @endif
</div>
