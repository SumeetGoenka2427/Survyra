@php
    $min = $question->settings['scale_min'] ?? 1;
    $max = $question->settings['scale_max'] ?? 5;
    $rows = $question->options ?? [];
@endphp
<div class="table-responsive mb-2">
    <table class="table sq-matrix-table">
        <thead>
            <tr>
                <th></th>
                @for ($col = $min; $col <= $max; $col++)
                    <th class="text-center">{{ $col }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $rowIndex => $rowLabel)
                <tr>
                    <td>{{ $rowLabel }}</td>
                    @for ($col = $min; $col <= $max; $col++)
                        <td class="text-center">
                            <input
                                type="radio"
                                name="answer-{{ $question->id }}-row-{{ $rowIndex }}"
                                id="answer-{{ $question->id }}-{{ $rowIndex }}-{{ $col }}"
                                value="{{ $col }}"
                                data-matrix-row="{{ $rowIndex }}"
                                autocomplete="off"
                                @required($question->is_required)
                            >
                        </td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="sq-scale-labels">
    <span>{{ $question->settings['low_label'] ?? 'Poor' }}</span>
    <span>{{ $question->settings['high_label'] ?? 'Excellent' }}</span>
</div>
