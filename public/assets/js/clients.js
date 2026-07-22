(function () {
    const filtersForm = document.getElementById('clients-filters');
    const fragment = document.getElementById('clients-fragment');
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

    function currentFilters() {
        return new URLSearchParams(new FormData(filtersForm));
    }

    function refresh(url) {
        const target = url || `${dataUrl}?${currentFilters().toString()}`;

        return jsonFetch(target).then((data) => {
            fragment.innerHTML = data.html;
        });
    }

    let debounce = null;
    filtersForm.addEventListener('input', function (event) {
        if (event.target.name !== 'search') return;
        clearTimeout(debounce);
        debounce = setTimeout(() => refresh(), 300);
    });

    filtersForm.addEventListener('change', function (event) {
        if (event.target.name === 'status') refresh();
    });

    fragment.addEventListener('click', function (event) {
        const pageLink = event.target.closest('.clients-pagination a');
        if (pageLink) {
            event.preventDefault();
            refresh(pageLink.href);
            return;
        }

        const toggleButton = event.target.closest('[data-toggle-client-status]');
        if (toggleButton) {
            jsonFetch(toggleButton.dataset.url, { method: 'PATCH' }).then((data) => {
                fragment.innerHTML = data.html;
                Toast.success('Client status updated.');
            });
            return;
        }

        const deleteButton = event.target.closest('[data-delete-client]');
        if (deleteButton) {
            if (!confirm('Remove this client? This cannot be undone.')) return;

            jsonFetch(deleteButton.dataset.url, { method: 'DELETE' }).then((data) => {
                fragment.innerHTML = data.html;
                Toast.success('Client removed.');
            });
        }
    });
})();
