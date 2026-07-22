@php
    $min = $question->settings['scale_min'] ?? 1;
    $max = $question->settings['scale_max'] ?? 5;
    $rows = $question->options ?? [];
@endphp
<div class="mb-2">
    @foreach ($rows as $rowIndex => $rowLabel)
        <div class="sq-matrix-stacked-row mb-3">
            <div class="small fw-semibold mb-2">{{ $rowLabel }}</div>
            <div class="sq-nps-row">
                @for ($col = $min; $col <= $max; $col++)
                    <input
                        type="radio"
                        class="sq-option-input"
                        name="answer-{{ $question->id }}-row-{{ $rowIndex }}"
                        id="answer-{{ $question->id }}-{{ $rowIndex }}-{{ $col }}"
                        value="{{ $col }}"
                        data-matrix-row="{{ $rowIndex }}"
                        autocomplete="off"
                        @required($question->is_required)
                    >
                    <label class="sq-btn" for="answer-{{ $question->id }}-{{ $rowIndex }}-{{ $col }}">{{ $col }}</label>
                @endfor
            </div>
        </div>
    @endforeach
</div>
<div class="sq-scale-labels">
    <span>{{ $question->settings['low_label'] ?? 'Poor' }}</span>
    <span>{{ $question->settings['high_label'] ?? 'Excellent' }}</span>
</div>
