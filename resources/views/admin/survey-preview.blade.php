@php
    $totalSteps = $steps->count();
    $isCardBased = $layout === 'card_based';
    $isSectionWizard = $layout === 'section_wizard';
    $isGrouped = in_array($layout, ['one_page', 'card_based', 'section_wizard'], true);
    $containerWidth = match ($layout) {
        'card_based' => '720px',
        'one_page', 'section_wizard' => '640px',
        default => '480px',
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Preview - {{ $label }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="{{ asset('assets/css/survey-experience.css') }}">
    @include('survey._theme-vars', ['theme' => $theme])
    <style>
        .preview-toolbar {
            background: #0f172a;
            color: #fff;
            padding: 0.6rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            font-family: 'Inter', system-ui, sans-serif;
        }
        .preview-toolbar select {
            background: rgba(255,255,255,0.1);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 6px;
            padding: 0.3rem 0.6rem;
            font-size: 0.85rem;
        }
        .preview-toolbar select option { color: #0f172a; }
    </style>
</head>
<body>
    <div class="preview-toolbar">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-warning text-dark"><i class="bi bi-eye"></i> Preview Mode</span>
            <span class="small">
                {{ $label }} — responses here are not saved
            </span>
            <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $layout)) }} layout</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="small mb-0">Theme:</label>
            <select onchange="window.location.href = this.value">
                @foreach ($themes as $option)
                    <option
                        value="{{ route('admin.survey-preview', array_filter(['survey' => $survey?->id, 'template' => $template?->id, 'theme' => $option->id])) }}"
                        @selected($option->id === $theme->id)
                    >{{ $option->name }}</option>
                @endforeach
            </select>
            <button type="button" class="btn btn-sm btn-outline-light" onclick="window.close()">
                <i class="bi bi-x-lg"></i> Close
            </button>
        </div>
    </div>

    <div class="d-flex align-items-center justify-content-center py-5">
        <div class="container" style="max-width: {{ $containerWidth }};">
            <div class="card border-0 shadow-sm survey-card">
                <div class="card-body p-4">
                    <div id="preview-content">
                        @foreach ($steps as $stepIndex => $stepQuestions)
                            <div data-step="{{ $stepIndex }}" class="{{ $stepIndex === 0 ? '' : 'd-none' }}">
                                @if ($isSectionWizard)
                                    <div class="text-muted small mb-2">Section {{ $stepIndex + 1 }} of {{ $totalSteps }}</div>
                                    <div class="progress mb-4" style="height: 6px;">
                                        <div class="progress-bar" style="width: {{ $totalSteps ? round(($stepIndex + 1) / $totalSteps * 100) : 0 }}%;"></div>
                                    </div>
                                @elseif (! $isGrouped)
                                    <div class="text-muted small mb-2">Question {{ $stepIndex + 1 }} of {{ $totalSteps }}</div>
                                    <div class="progress mb-4" style="height: 6px;">
                                        <div class="progress-bar" style="width: {{ $totalSteps ? round($stepIndex / $totalSteps * 100) : 0 }}%;"></div>
                                    </div>
                                @else
                                    <div class="text-muted small mb-3">{{ $stepQuestions->count() }} question{{ $stepQuestions->count() === 1 ? '' : 's' }} shown all at once, just like respondents will see them.</div>
                                @endif

                                @if ($isCardBased)
                                    <div class="card-based-list">
                                        @foreach ($stepQuestions as $qIndex => $question)
                                            <div class="card-based-question">
                                                <div class="card-based-question-number">{{ $qIndex + 1 }}</div>
                                                <h5 class="sq-label">{{ $question->question_text }}</h5>
                                                @if (!empty($question->settings['help_text']))
                                                    <div class="text-muted small mb-2">{{ $question->settings['help_text'] }}</div>
                                                @endif
                                                @include($question->questionType->contract()->renderComponent($question->settings['display_style'] ?? 'default'), ['question' => $question])
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    @foreach ($stepQuestions as $question)
                                        <div class="mb-4">
                                            <h4 class="sq-label">{{ $question->question_text }}</h4>
                                            @if (!empty($question->settings['help_text']))
                                                <div class="text-muted small mb-2">{{ $question->settings['help_text'] }}</div>
                                            @endif
                                            @include($question->questionType->contract()->renderComponent($question->settings['display_style'] ?? 'default'), ['question' => $question])
                                        </div>
                                    @endforeach
                                @endif

                                <button type="button" class="btn btn-survyra-primary w-100 mt-3" data-preview-next>
                                    {{ $stepIndex === $totalSteps - 1 ? 'Finish' : 'Next' }}
                                </button>
                            </div>
                        @endforeach

                        <div data-step="{{ $totalSteps }}" class="{{ $totalSteps === 0 ? '' : 'd-none' }} text-center">
                            <div class="mb-3" style="font-size: 3rem;">🎉</div>
                            <p class="fs-5 mb-1">Thanks for previewing!</p>
                            <p class="text-muted small mb-4">This is what a respondent sees after finishing the survey.</p>
                            <button type="button" class="btn btn-outline-secondary btn-sm" data-preview-restart>
                                <i class="bi bi-arrow-repeat"></i> Restart preview
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const content = document.getElementById('preview-content');
            const totalSteps = {{ $totalSteps }};
            let step = 0;

            function show(index) {
                content.querySelectorAll('[data-step]').forEach((el) => {
                    el.classList.toggle('d-none', Number(el.dataset.step) !== index);
                });
                step = index;
            }

            content.addEventListener('click', function (event) {
                if (event.target.closest('[data-preview-next]')) {
                    show(Math.min(step + 1, totalSteps));
                }
                if (event.target.closest('[data-preview-restart]')) {
                    show(0);
                }
            });

            content.addEventListener('input', function (event) {
                if (event.target && event.target.hasAttribute('data-autosize')) {
                    event.target.style.height = 'auto';
                    event.target.style.height = `${event.target.scrollHeight}px`;
                }
            });
        })();
    </script>
</body>
</html>
