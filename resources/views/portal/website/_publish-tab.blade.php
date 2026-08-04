<div class="mb-4">
    <p>
        Status:
        <span class="badge text-bg-{{ $website->status === 'published' ? 'success' : 'warning' }}">{{ ucfirst($website->status) }}</span>
    </p>

    @if ($website->status === 'published')
        <p>Public URL: <a href="{{ route('website.show', $website->slug) }}" target="_blank" rel="noopener">{{ route('website.show', $website->slug) }}</a></p>
        @if ($website->published_at)
            <p class="text-muted small">Last published {{ $website->published_at->diffForHumans() }}.</p>
        @endif
        <form method="POST" action="{{ route('portal.website.unpublish') }}">
            @csrf
            <button type="submit" class="btn btn-outline-danger">Unpublish</button>
        </form>
    @else
        <p class="text-muted small">Your website is a draft and not visible to the public yet. Publishing takes a snapshot of your current pages and sections and makes it live.</p>
        <form method="POST" action="{{ route('portal.website.publish') }}">
            @csrf
            <button type="submit" class="btn btn-primary">Publish Website</button>
        </form>
    @endif
</div>
