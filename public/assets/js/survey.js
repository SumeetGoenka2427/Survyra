(function () {
    const app = document.getElementById('survey-app');
    const content = document.getElementById('survey-app-content');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    const slug = app.dataset.slug;
    const responseUuid = app.dataset.responseUuid;

    function collectAnswer(form) {
        const fileField = form.querySelector('input[type="file"][name="answer"]');
        if (fileField) {
            return fileField.files.length > 0 ? fileField.files[0] : null;
        }

        const matrixInputs = form.querySelectorAll('[data-matrix-row]:checked');
        if (matrixInputs.length > 0) {
            const rows = {};
            matrixInputs.forEach((el) => { rows[el.dataset.matrixRow] = el.value; });
            return rows;
        }

        const rankingOrder = form.querySelector('[data-ranking-order]');
        if (rankingOrder) {
            return rankingOrder.value ? rankingOrder.value.split(',') : [];
        }

        const checkboxes = form.querySelectorAll('input[name="answer[]"]:checked');
        if (checkboxes.length > 0) {
            return Array.from(checkboxes).map((el) => el.value);
        }

        const checked = form.querySelector('input[name="answer"]:checked');
        if (checked) {
            return checked.value;
        }

        const field = form.querySelector('[name="answer"]');
        return field ? field.value : null;
    }

    // ---- Ranking question: up/down reorder, no drag-and-drop needed --------
    function updateRankingOrder(list) {
        const items = Array.from(list.querySelectorAll('[data-ranking-item]'));
        items.forEach((el, index) => {
            const rankBadge = el.querySelector('.sq-ranking-rank');
            if (rankBadge) rankBadge.textContent = index + 1;
        });

        const hidden = list.parentElement.querySelector('[data-ranking-order]');
        if (hidden) {
            hidden.value = items.map((el) => el.dataset.value).join(',');
            // Autosave (one-page/card-based/section-wizard) listens for native
            // "change" bubbling - programmatic .value writes don't fire it.
            hidden.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    content.addEventListener('click', function (event) {
        const upBtn = event.target.closest('[data-ranking-up]');
        const downBtn = event.target.closest('[data-ranking-down]');
        if (!upBtn && !downBtn) return;

        const item = event.target.closest('[data-ranking-item]');
        const list = item && item.closest('[data-ranking-list]');
        if (!list) return;

        if (upBtn && item.previousElementSibling) {
            list.insertBefore(item, item.previousElementSibling);
        } else if (downBtn && item.nextElementSibling) {
            list.insertBefore(item.nextElementSibling, item);
        }

        updateRankingOrder(list);
    });

    // File answers can't travel as JSON - posted as multipart instead. Every
    // /answer call site shares this so file_upload questions work everywhere
    // a regular answer would (submit, back, one-page autosave).
    function postAnswer(questionId, answer) {
        if (answer instanceof File) {
            const formData = new FormData();
            formData.append('response_uuid', responseUuid);
            formData.append('question_id', questionId);
            formData.append('answer', answer);

            return fetch(`/s/${slug}/answer`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, Accept: 'application/json' },
                credentials: 'same-origin',
                body: formData,
            });
        }

        return fetch(`/s/${slug}/answer`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                Accept: 'application/json',
            },
            credentials: 'same-origin',
            body: JSON.stringify({ response_uuid: responseUuid, question_id: questionId, answer: answer }),
        });
    }

    function clearErrors(form) {
        form.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
        const existing = form.querySelector('.survey-error');
        if (existing) existing.remove();
    }

    function showError(form, message) {
        const alert = document.createElement('div');
        alert.className = 'alert alert-danger survey-error mt-2';
        alert.textContent = message;
        form.appendChild(alert);
    }

    function autosize(el) {
        el.style.height = 'auto';
        el.style.height = `${el.scrollHeight}px`;
    }

    async function submitAnswer(event) {
        event.preventDefault();
        const form = event.target;
        const questionId = form.dataset.questionId;
        const answer = collectAnswer(form);
        clearErrors(form);

        const submitButton = form.querySelector('button[type="submit"]');
        const originalLabel = submitButton.innerHTML;
        submitButton.disabled = true;
        submitButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

        try {
            const response = await postAnswer(questionId, answer);

            if (response.status === 422) {
                const data = await response.json();
                const message = Object.values(data.errors || {})[0]?.[0] || 'Please check your answer and try again.';
                showError(form, message);
                submitButton.disabled = false;
                submitButton.innerHTML = originalLabel;
                return;
            }

            if (!response.ok) {
                showError(form, 'Something went wrong. Please try again.');
                submitButton.disabled = false;
                submitButton.innerHTML = originalLabel;
                return;
            }

            const data = await response.json();
            content.innerHTML = data.html;
        } catch (error) {
            showError(form, 'Network error - please check your connection and try again.');
            submitButton.disabled = false;
            submitButton.innerHTML = originalLabel;
        }
    }

    // ---- Back button handler ----
    async function goBack() {
        const form = document.getElementById('answer-form');
        if (!form) return;

        const questionId = form.dataset.questionId;
        const answer = collectAnswer(form);

        // Save current answer before going back
        try {
            await postAnswer(questionId, answer);
        } catch (e) {
            // Continue even if save fails - the back button should still work
        }

        try {
            const response = await fetch(`/s/${slug}/back`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ response_uuid: responseUuid }),
            });

            if (response.ok) {
                const data = await response.json();
                content.innerHTML = data.html;
            }
        } catch (e) {
            // Ignore network errors
        }
    }

    content.addEventListener('click', function (event) {
        const backBtn = event.target.closest('#back-button');
        if (backBtn) {
            event.preventDefault();
            goBack();
            return;
        }
    });

    content.addEventListener('submit', function (event) {
        if (event.target && event.target.id === 'answer-form') {
            submitAnswer(event);
        }

        // One-page questions have no submit button of their own (autosave
        // handles them) - only guard against the implicit Enter-key submit
        // browsers perform on a lone text field.
        if (event.target && event.target.classList.contains('one-page-answer-form')) {
            event.preventDefault();
        }
    });

    content.addEventListener('input', function (event) {
        if (event.target && event.target.hasAttribute('data-autosize')) {
            autosize(event.target);
        }
    });

    // ---- One-page / card-based / section-wizard layouts: questions autosave -
    // ---- independently, and the fragment swap doubles as "did the visible ---
    // ---- set change" detection - which is also how section-wizard auto- -----
    // ---- advances to the next section once its required fields are done ----
    if (['one_page', 'card_based', 'section_wizard'].includes(app.dataset.layout)) {
        function saveOnePageField(form) {
            const questionId = form.dataset.questionId;
            const answer = collectAnswer(form);
            const status = form.querySelector('.one-page-save-status');

            if (status) {
                status.textContent = 'Saving…';
                status.classList.remove('text-success', 'text-danger');
            }

            postAnswer(questionId, answer)
                .then(async (response) => {
                    if (response.status === 422) {
                        const data = await response.json();
                        if (status) {
                            status.textContent = Object.values(data.errors || {})[0]?.[0] || 'Please check your answer.';
                            status.classList.add('text-danger');
                        }
                        return;
                    }

                    if (!response.ok) {
                        if (status) {
                            status.textContent = 'Could not save - check your connection.';
                            status.classList.add('text-danger');
                        }
                        return;
                    }

                    const data = await response.json();
                    const list = document.getElementById('one-page-list');
                    const currentIds = list ? list.dataset.questionIds : '';
                    const newIds = (data.questionIds || []).join(',');

                    if (newIds !== currentIds) {
                        // Logic rules changed which questions apply - re-render the set.
                        content.innerHTML = data.html;
                    } else if (status) {
                        status.textContent = 'Saved';
                        status.classList.add('text-success');
                        setTimeout(() => { status.textContent = ''; }, 1500);
                    }
                })
                .catch(() => {
                    if (status) {
                        status.textContent = 'Network error.';
                        status.classList.add('text-danger');
                    }
                });
        }

        function submitOnePage() {
            const errorBox = document.getElementById('one-page-submit-error');
            const submitButton = document.getElementById('one-page-submit');
            if (errorBox) errorBox.classList.add('d-none');
            submitButton.disabled = true;

            fetch(`/s/${slug}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    Accept: 'application/json',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ response_uuid: responseUuid }),
            })
                .then(async (response) => {
                    if (response.status === 422) {
                        const data = await response.json();
                        const message = Object.values(data.errors || {})[0]?.[0] || 'Please answer all required questions before submitting.';
                        if (errorBox) {
                            errorBox.textContent = message;
                            errorBox.classList.remove('d-none');
                        }
                        submitButton.disabled = false;
                        return;
                    }

                    const data = await response.json();
                    content.innerHTML = data.html;
                })
                .catch(() => {
                    if (errorBox) {
                        errorBox.textContent = 'Network error - please try again.';
                        errorBox.classList.remove('d-none');
                    }
                    submitButton.disabled = false;
                });
        }

        content.addEventListener('change', function (event) {
            const form = event.target.closest('.one-page-answer-form');
            if (form) saveOnePageField(form);
        });

        // blur doesn't bubble, so this needs the capture phase.
        content.addEventListener('blur', function (event) {
            const target = event.target;
            if (!target || !target.closest) return;

            const form = target.closest('.one-page-answer-form');
            const isTextish = target.tagName === 'TEXTAREA'
                || (target.tagName === 'INPUT' && !['radio', 'checkbox'].includes(target.type));

            if (form && isTextish) saveOnePageField(form);
        }, true);

        content.addEventListener('click', function (event) {
            if (event.target.closest('#one-page-submit')) {
                submitOnePage();
            }
        });
    }
})();
