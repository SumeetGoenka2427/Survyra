<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #222; }
        h1 { font-size: 18px; margin-bottom: 0; }
        h2 { font-size: 14px; margin-top: 24px; }
        .meta { color: #666; margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-size: 11px; }
        th { background: #f4f4f4; }
        .stat-row td { border: none; padding: 4px 8px; }
    </style>
</head>
<body>
    <h1>{{ $client->company_name }}</h1>
    <div class="meta">
        {{ $survey?->title ?? 'All surveys' }} &middot; {{ $from->toFormattedDateString() }} - {{ $to->toFormattedDateString() }}
    </div>

    <h2>Summary</h2>
    <table>
        <tr class="stat-row"><td><strong>Total Responses</strong></td><td>{{ $snapshot['total_responses'] }}</td></tr>
        <tr class="stat-row"><td><strong>Completion Rate</strong></td><td>{{ $snapshot['completion_rate'] }}%</td></tr>
        @if ($snapshot['avg_completion_seconds'])
            <tr class="stat-row"><td><strong>Average Completion Time</strong></td><td>{{ gmdate('i:s', $snapshot['avg_completion_seconds']) }}</td></tr>
        @endif
        @foreach ($snapshot['metrics'] as $key => $metric)
            <tr class="stat-row"><td><strong>{{ strtoupper($key) }}</strong></td><td>{{ $metric['value'] }}{{ $key === 'nps' ? '' : '%' }}</td></tr>
        @endforeach
        <tr class="stat-row"><td><strong>Positive / Neutral / Negative</strong></td>
            <td>{{ $snapshot['sentiment_counts']['positive'] }} / {{ $snapshot['sentiment_counts']['neutral'] }} / {{ $snapshot['sentiment_counts']['negative'] }}</td>
        </tr>
    </table>

    <h2>Responses</h2>
    <table>
        <thead>
            <tr>
                <th>Survey</th>
                <th>Status</th>
                <th>Score</th>
                <th>Sentiment</th>
                <th>Started</th>
                <th>Completed</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($responses as $response)
                <tr>
                    <td>{{ $response->survey->title }}</td>
                    <td>{{ ucfirst($response->status) }}</td>
                    <td>{{ $response->score ?? '—' }}</td>
                    <td>{{ $response->sentiment ? ucfirst($response->sentiment) : '—' }}</td>
                    <td>{{ optional($response->started_at)->format('Y-m-d H:i') }}</td>
                    <td>{{ optional($response->completed_at)->format('Y-m-d H:i') ?? '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
