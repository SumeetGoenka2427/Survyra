@php $welcomeScreen = $survey->welcome_screen ?? []; @endphp
<div class="text-center">
    <h4 class="sq-label">{{ $welcomeScreen['title'] ?? 'Welcome' }}</h4>
    @if (!empty($welcomeScreen['description']))
        <p class="text-muted mb-4">{{ $welcomeScreen['description'] }}</p>
    @endif
    <a href="{{ request()->fullUrlWithQuery(['start' => 1]) }}" class="btn btn-survyra-primary w-100">
        {{ $welcomeScreen['button_text'] ?? 'Start' }}
    </a>
</div>
