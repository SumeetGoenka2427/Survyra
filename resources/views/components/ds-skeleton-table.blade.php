@props(['rows' => 5, 'cols' => 4])
<div class="card border-0">
    <div class="table-responsive">
        <table class="table mb-0">
            <tbody>
                @for ($i = 0; $i < $rows; $i++)
                    <tr>
                        @for ($j = 0; $j < $cols; $j++)
                            <td class="py-3"><div class="ds-skeleton ds-skeleton-line" style="width: {{ [70, 85, 60, 90, 50][($i + $j) % 5] }}%"></div></td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>
