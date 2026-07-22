(function () {
    const filtersForm = document.getElementById('surveys-filters');
    const fragment = document.getElementById('surveys-fragment');
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
        }).then((response) => response.json().then((data) => ({ ok: response.ok, data })));
    }

    function currentFilters() {
        return new URLSearchParams(new FormData(filtersForm));
    }

    function refresh(url) {
        const target = url || `${dataUrl}?${currentFilters().toString()}`;

        return jsonFetch(target).then(({ data }) => {
            fragment.innerHTML = data.html;
        });
    }

    filtersForm.addEventListener('change', function (event) {
        if (event.target.name === 'client_id' || event.target.name === 'status') refresh();
    });

    fragment.addEventListener('click', function (event) {
        const pageLink = event.target.closest('.surveys-pagination a');
        if (pageLink) {
            event.preventDefault();
            refresh(pageLink.href);
            return;
        }

        const publishButton = event.target.closest('[data-survey-publish]');
        if (publishButton) {
            jsonFetch(publishButton.dataset.url, { method: 'POST' }).then(({ data }) => {
                fragment.innerHTML = data.html;
                Toast.success('Survey published.');
            });
            return;
        }

        const archiveButton = event.target.closest('[data-survey-archive]');
        if (archiveButton) {
            if (!confirm('Archive this survey? It will stop being editable as a draft.')) return;

            jsonFetch(archiveButton.dataset.url, { method: 'POST' }).then(({ data }) => {
                fragment.innerHTML = data.html;
                Toast.success('Survey archived.');
            });
            return;
        }

        const deleteButton = event.target.closest('[data-survey-delete]');
        if (deleteButton) {
            if (!confirm('Delete this draft survey? This cannot be undone.')) return;

            jsonFetch(deleteButton.dataset.url, { method: 'DELETE' }).then(({ ok, data }) => {
                if (!ok) {
                    Toast.error(data.message || 'Could not delete this survey.');
                    return;
                }
                fragment.innerHTML = data.html;
                Toast.success('Survey removed.');
            });
        }
    });
})();
