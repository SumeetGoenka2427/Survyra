(function () {
    const filtersForm = document.getElementById('themes-filters');
    const fragment = document.getElementById('themes-fragment');
    if (!filtersForm || !fragment) return;

    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const dataUrl = filtersForm.dataset.dataUrl;

    function jsonFetch(url, options = {}) {
        return fetch(url, {
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                ...(options.headers || {}),
            },
            ...options,
        }).then((response) => response.json());
    }

    function refresh() {
        const params = new URLSearchParams(new FormData(filtersForm));
        return jsonFetch(`${dataUrl}?${params.toString()}`).then((data) => {
            fragment.innerHTML = data.html;
        });
    }

    let debounce = null;
    filtersForm.addEventListener('input', function (event) {
        if (event.target.name !== 'search') return;
        clearTimeout(debounce);
        debounce = setTimeout(refresh, 300);
    });

    fragment.addEventListener('click', function (event) {
        const deleteButton = event.target.closest('[data-theme-delete]');
        if (deleteButton) {
            if (!confirm('Delete this theme? Any surveys using it will fall back to no theme.')) return;

            jsonFetch(deleteButton.dataset.url, { method: 'DELETE' }).then((data) => {
                fragment.innerHTML = data.html;
                Toast.success('Theme removed.');
            });
        }
    });
})();
