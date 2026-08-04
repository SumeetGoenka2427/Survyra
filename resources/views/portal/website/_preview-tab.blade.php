<div x-data="{ device: 'desktop' }">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="btn-group" role="group" aria-label="Preview device size">
            <button type="button" class="btn btn-sm btn-outline-secondary" :class="device === 'desktop' ? 'active' : ''" @click="device = 'desktop'">
                <i class="bi bi-display"></i> Desktop
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" :class="device === 'tablet' ? 'active' : ''" @click="device = 'tablet'">
                <i class="bi bi-tablet"></i> Tablet
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" :class="device === 'mobile' ? 'active' : ''" @click="device = 'mobile'">
                <i class="bi bi-phone"></i> Mobile
            </button>
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('website-preview-frame').src = document.getElementById('website-preview-frame').src">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>

    <div class="text-center bg-light rounded p-3" style="overflow-x: auto;">
        <div class="mx-auto" :style="'width: ' + (device === 'desktop' ? '100%' : device === 'tablet' ? '800px' : '390px') + '; max-width: 100%; transition: width 0.2s ease;'">
            <iframe
                id="website-preview-frame"
                data-src="{{ route('portal.website.preview') }}"
                title="Website preview"
                class="w-100 border rounded bg-white"
                style="height: 720px;"
            ></iframe>
        </div>
    </div>
    <p class="text-muted small mt-2">Shows your current draft, including unpublished changes. Contact form submissions are disabled in preview.</p>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('tab-preview-btn');
    var frame = document.getElementById('website-preview-frame');
    if (!btn || !frame) return;

    // Lazy-load the iframe only when the Preview tab is actually opened, so it
    // always reflects the latest draft (every builder edit is a full page
    // reload back here) rather than a stale snapshot loaded on page load.
    btn.addEventListener('shown.bs.tab', function () {
        frame.src = frame.dataset.src;
    });
});
</script>
