(function () {
    const ICONS = {
        success: 'bi-check-circle-fill',
        error: 'bi-x-circle-fill',
        warning: 'bi-exclamation-triangle-fill',
        info: 'bi-info-circle-fill',
    };

    function ensureStack() {
        let stack = document.getElementById('ds-toast-stack');
        if (!stack) {
            stack = document.createElement('div');
            stack.id = 'ds-toast-stack';
            document.body.appendChild(stack);
        }
        return stack;
    }

    function show(message, type = 'info', duration = 4000) {
        const stack = ensureStack();
        const toast = document.createElement('div');
        toast.className = `ds-toast ${type}`;
        toast.innerHTML = `
            <i class="bi ${ICONS[type] || ICONS.info} ds-toast-icon"></i>
            <div class="flex-grow-1">${message}</div>
            <button type="button" class="btn-close btn-close-sm" aria-label="Close"></button>
        `;

        function dismiss() {
            toast.classList.add('leaving');
            setTimeout(() => toast.remove(), 200);
        }

        toast.querySelector('.btn-close').addEventListener('click', dismiss);
        stack.appendChild(toast);

        if (duration > 0) {
            setTimeout(dismiss, duration);
        }

        return { dismiss };
    }

    window.Toast = {
        show,
        success: (message, duration) => show(message, 'success', duration),
        error: (message, duration) => show(message, 'error', duration),
        warning: (message, duration) => show(message, 'warning', duration),
        info: (message, duration) => show(message, 'info', duration),
    };
})();
