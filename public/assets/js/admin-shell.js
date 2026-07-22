(function () {
    const csrfMeta = document.querySelector('meta[name="csrf-token"]');
    const csrfToken = csrfMeta ? csrfMeta.content : '';

    // ---- Notifications --------------------------------------------------------
    const notificationList = document.getElementById('notification-list');

    if (notificationList) {
        notificationList.addEventListener('click', function (event) {
            const item = event.target.closest('[data-mark-read]');
            if (!item) return;

            event.preventDefault();
            const id = item.dataset.notificationId;
            const url = notificationList.dataset.markReadUrlTemplate.replace('__ID__', id);
            const destination = item.getAttribute('href');

            fetch(url, {
                method: 'POST',
                keepalive: true,
                credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
            })
                .then((response) => response.json())
                .then((data) => {
                    notificationList.innerHTML = data.html;
                    const badge = document.getElementById('notification-unread-badge');
                    if (badge) {
                        if (data.unreadCount > 0) {
                            badge.textContent = data.unreadCount > 9 ? '9+' : data.unreadCount;
                        } else {
                            badge.remove();
                        }
                    }
                })
                .finally(() => {
                    if (destination && destination !== '#') window.location.href = destination;
                });
        });
    }

    // ---- Keyboard shortcuts: Ctrl+S saves the nearest form ------------------
    // Esc-closes-modal is already native Bootstrap behavior (modal `keyboard`
    // option defaults to true) - nothing to add there.
    document.addEventListener('keydown', function (event) {
        if (!(event.ctrlKey || event.metaKey) || event.key.toLowerCase() !== 's') return;

        const activeForm = document.activeElement && document.activeElement.closest('form');
        const form = activeForm || document.querySelector('main form');
        if (!form) return;

        event.preventDefault();
        if (form.requestSubmit) {
            form.requestSubmit();
        } else {
            form.submit();
        }
    });

    // ---- Sidebar collapse -------------------------------------------------
    const sidebar = document.getElementById('ds-sidebar');
    const collapseBtn = document.getElementById('ds-sidebar-toggle');

    if (sidebar && collapseBtn) {
        if (localStorage.getItem('ds-sidebar-collapsed') === '1') {
            sidebar.classList.add('ds-collapsed');
        }

        collapseBtn.addEventListener('click', function () {
            sidebar.classList.toggle('ds-collapsed');
            localStorage.setItem('ds-sidebar-collapsed', sidebar.classList.contains('ds-collapsed') ? '1' : '0');
        });
    }

    // ---- Theme toggle -------------------------------------------------------
    const themeToggle = document.getElementById('ds-theme-toggle');
    const root = document.documentElement;

    function applyTheme(theme) {
        root.setAttribute('data-bs-theme', theme);
        if (themeToggle) {
            themeToggle.querySelector('i').className = theme === 'dark' ? 'bi bi-sun' : 'bi bi-moon-stars';
        }
    }

    applyTheme(localStorage.getItem('ds-theme') || 'light');

    if (themeToggle) {
        themeToggle.addEventListener('click', function () {
            const next = root.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
            localStorage.setItem('ds-theme', next);
            applyTheme(next);
        });
    }

    // ---- Command palette (global search) ---------------------------------------
    const searchTrigger = document.getElementById('ds-search-trigger');
    const overlay = document.getElementById('command-palette-overlay');
    const paletteInput = document.getElementById('command-palette-input');
    const paletteResults = document.getElementById('command-palette-results');

    if (overlay && paletteInput && paletteResults) {
        let debounce = null;
        let items = [];
        let activeIndex = -1;

        function flatten(groups) {
            return groups.flatMap((group) => group.items.map((item) => ({ ...item, groupLabel: group.label, groupIcon: group.icon })));
        }

        function render(groups) {
            items = flatten(groups);
            activeIndex = items.length ? 0 : -1;

            if (!items.length) {
                paletteResults.innerHTML = '<div class="command-palette-empty">No matches found.</div>';
                return;
            }

            let index = 0;
            paletteResults.innerHTML = groups.map((group) => `
                <div class="command-palette-group-label"><i class="bi ${group.icon} me-1"></i>${group.label}</div>
                ${group.items.map((item) => `<a href="${item.url}" class="command-palette-item" data-index="${index++}">${item.title}</a>`).join('')}
            `).join('');

            highlight();
        }

        function highlight() {
            paletteResults.querySelectorAll('.command-palette-item').forEach((el) => {
                el.classList.toggle('active', Number(el.dataset.index) === activeIndex);
            });
        }

        function openPalette() {
            overlay.classList.remove('d-none');
            paletteInput.value = '';
            paletteResults.innerHTML = '<div class="command-palette-empty">Start typing to search...</div>';
            items = [];
            activeIndex = -1;
            setTimeout(() => paletteInput.focus(), 0);
        }

        function closePalette() {
            overlay.classList.add('d-none');
            if (searchTrigger) searchTrigger.blur();
        }

        if (searchTrigger) {
            searchTrigger.addEventListener('focus', openPalette);
            searchTrigger.addEventListener('click', openPalette);
        }

        overlay.addEventListener('click', function (event) {
            if (event.target === overlay) closePalette();
        });

        paletteInput.addEventListener('input', function () {
            const term = paletteInput.value.trim();
            clearTimeout(debounce);

            if (term.length < 2) {
                paletteResults.innerHTML = '<div class="command-palette-empty">Start typing to search...</div>';
                items = [];
                return;
            }

            debounce = setTimeout(function () {
                fetch(`${paletteInput.dataset.searchUrl}?q=${encodeURIComponent(term)}`, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                })
                    .then((response) => response.json())
                    .then((data) => render(data.groups || []));
            }, 250);
        });

        paletteInput.addEventListener('keydown', function (event) {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                if (items.length) activeIndex = (activeIndex + 1) % items.length;
                highlight();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                if (items.length) activeIndex = (activeIndex - 1 + items.length) % items.length;
                highlight();
            } else if (event.key === 'Enter') {
                event.preventDefault();
                if (items[activeIndex]) window.location.href = items[activeIndex].url;
            }
        });

        document.addEventListener('keydown', function (event) {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                openPalette();
            } else if (event.key === 'Escape' && !overlay.classList.contains('d-none')) {
                closePalette();
            }
        });
    }
})();
