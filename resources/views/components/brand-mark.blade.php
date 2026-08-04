@props(['variant' => 'light'])
<span class="sv-brand-lockup d-inline-flex align-items-center gap-2">
    <img src="{{ asset('assets/images/logo-mark.svg') }}" alt="" width="32" height="32" class="sv-brand-icon">
    <span class="sv-brand {{ $variant === 'dark' ? 'sv-brand-on-dark' : '' }}">Survyra</span>
</span>
