<x-mail::message>
# Weekly Digest for {{ $client->company_name }}

**Period:** {{ $from->format('M j') }} – {{ $to->format('M j, Y') }}

---

## Overview

@if (isset($digest['overview']))
{{ $digest['overview'] }}
@else
Your surveys received **{{ $snapshot['total_responses'] }}** responses this week with a **{{ $snapshot['completion_rate'] }}%** completion rate.
@endif

---

## Key Metrics

<x-mail::table>
| Metric | Value |
|--------|-------:|
| Total Responses | {{ $snapshot['total_responses'] }} |
| Completion Rate | {{ $snapshot['completion_rate'] }}% |
| Avg. Completion Time | {{ $snapshot['avg_completion_seconds'] ? gmdate('i:s', $snapshot['avg_completion_seconds']) : 'N/A' }} |
| Positive Feedback | {{ $snapshot['sentiment_counts']['positive'] ?? 0 }} |
| Neutral Feedback | {{ $snapshot['sentiment_counts']['neutral'] ?? 0 }} |
| Negative Feedback | {{ $snapshot['sentiment_counts']['negative'] ?? 0 }} |
</x-mail::table>

@if (isset($digest['top_insights']) && count($digest['top_insights']) > 0)
## Top Insights

@foreach ($digest['top_insights'] as $insight)
- {{ $insight }}
@endforeach
@endif

@if (isset($digest['recommendations']) && count($digest['recommendations']) > 0)
## Recommendations

@foreach ($digest['recommendations'] as $recommendation)
- {{ $recommendation }}
@endforeach
@endif

<x-mail::button :url="route('portal.dashboard')">
View Full Dashboard
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>