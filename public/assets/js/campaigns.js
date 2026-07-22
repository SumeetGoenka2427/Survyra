(function () {
    const filtersForm = document.getElementById('campaigns-filters');
    const fragment = document.getElementById('campaigns-fragment');
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

    function refresh(url) {
        const target = url || `${dataUrl}?${new URLSearchParams(new FormData(filtersForm)).toString()}`;
        return jsonFetch(target).then((data) => {
            fragment.innerHTML = data.html;
        });
    }

    filtersForm.addEventListener('change', function (event) {
        if (event.target.name === 'client_id') refresh();
    });

    fragment.addEventListener('click', function (event) {
        const pageLink = event.target.closest('.campaigns-pagination a');
        if (pageLink) {
            event.preventDefault();
            refresh(pageLink.href);
            return;
        }

        const sendButton = event.target.closest('[data-campaign-send]');
        if (sendButton) {
            if (!confirm('Send this campaign now?')) return;

            jsonFetch(sendButton.dataset.url, { method: 'POST' }).then((data) => {
                fragment.innerHTML = data.html;
                Toast.success('Campaign is sending.');
            });
            return;
        }

        const retryButton = event.target.closest('[data-campaign-retry]');
        if (retryButton) {
            jsonFetch(retryButton.dataset.url, { method: 'POST' }).then((data) => {
                fragment.innerHTML = data.html;
                Toast.success('Retrying failed recipients.');
            });
        }
    });
})();
