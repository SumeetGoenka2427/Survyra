@if ($notifications->isEmpty())
    <div class="text-center text-muted small py-4">
        <i class="bi bi-bell-slash fs-4 d-block mb-2"></i>
        No notifications yet.
    </div>
@else
    @foreach ($notifications as $notification)
        <a
            href="{{ $notification->data['url'] ?? '#' }}"
            class="d-block px-3 py-2 text-decoration-none text-body border-bottom {{ $notification->read_at ? '' : 'bg-primary-subtle' }}"
            data-notification-id="{{ $notification->id }}"
            @if (! $notification->read_at) data-mark-read @endif
        >
            <div class="small">{{ $notification->data['message'] ?? 'Notification' }}</div>
            <div class="text-muted" style="font-size: 0.72rem;">{{ $notification->created_at->diffForHumans() }}</div>
        </a>
    @endforeach
@endif
