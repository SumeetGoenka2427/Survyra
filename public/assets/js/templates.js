(function () {
    const filtersForm = document.getElementById('templates-filters');
    const fragment = document.getElementById('templates-fragment');
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
        const duplicateButton = event.target.closest('[data-template-duplicate]');
        if (duplicateButton) {
            jsonFetch(duplicateButton.dataset.url, { method: 'POST' }).then((data) => {
                fragment.innerHTML = data.html;
                Toast.success('Template duplicated. Opening it for editing...');
                setTimeout(() => { window.location.href = data.editUrl; }, 600);
            });
            return;
        }

        const deleteButton = event.target.closest('[data-template-delete]');
        if (deleteButton) {
            if (!confirm('Delete this template? This cannot be undone.')) return;

            jsonFetch(deleteButton.dataset.url, { method: 'DELETE' }).then((data) => {
                fragment.innerHTML = data.html;
                Toast.success('Template removed.');
            });
        }
    });
})();
