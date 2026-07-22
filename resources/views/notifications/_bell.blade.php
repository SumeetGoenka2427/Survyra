<div class="dropdown">
    <button type="button" class="ds-icon-btn position-relative" data-bs-toggle="dropdown" title="Notifications">
        <i class="bi bi-bell"></i>
        @if ($unreadCount > 0)
            <span class="position-absolute top-0 end-0 badge rounded-pill bg-danger" id="notification-unread-badge" style="font-size: 0.55rem; padding: 0.25em 0.45em;">
                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
            </span>
        @endif
    </button>
    <div class="dropdown-menu dropdown-menu-end shadow p-0" style="width: 340px; max-height: 420px; overflow-y: auto;">
        <div class="px-3 py-2 border-bottom fw-semibold small">Notifications</div>
        <div id="notification-list" data-mark-read-url-template="{{ $markReadUrlTemplate }}">
            @include('notifications._items', ['notifications' => $notifications])
        </div>
    </div>
</div>
