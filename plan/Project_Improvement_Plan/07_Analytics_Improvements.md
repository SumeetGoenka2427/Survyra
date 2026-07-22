# 07 – Analytics Improvements

> Detailed plan to bring analytics up to industry standard.

---

## Current State

The `AnalyticsService` computes:
- Total responses, today's responses
- Completion rate
- Average completion time (seconds)
- Sentiment counts (positive/neutral/negative)
- NPS, CSAT, CES, Rating metrics
- Question breakdown (choice counts, scale averages, text samples)
- Daily trend (labels + series)
- Review click breakdown by channel

**What's missing**: Drop-off, geo, device/browser/source charts, real-time, cross-filter, AI summary, time comparison.

---

## 1. Real-Time Dashboard

**What**: Auto-refresh response count and today's stats every 30–60 seconds.

**Implementation**:
```php
// Option A: Simple polling (recommended for MVP)
// Frontend: setInterval(() => fetch('/analytics/data?live=1'), 30000)

// Option B: Laravel Echo + Pusher
// Broadcast ResponseCompleted event on survey submit
```

**DB impact**: None — uses existing queries.

**Frontend**: Add a "Live" indicator badge that pulses when auto-refresh is active.

---

## 2. Drop-Off Analysis

**What**: Show at which question respondents abandon the survey.

**Implementation**:
- Add `last_question_id` to `responses` table.
- Update `ResponseService::saveAnswer()` to set `last_question_id` on every answer save.
- Analytics query:

```php
$dropOff = Response::query()
    ->where('survey_id', $survey->id)
    ->where('status', '!=', 'completed')
    ->whereNotNull('last_question_id')
    ->selectRaw('last_question_id, count(*) as drop_count')
    ->groupBy('last_question_id')
    ->pluck('drop_count', 'last_question_id');
```

**Visualization**: Funnel chart showing response count per question step.

---

## 3. Device / Browser / Source Charts

**What**: Pie/donut charts for device type, browser, and response source.

**Implementation** (data already stored):
```php
$devices = Response::query()
    ->whereIn('survey_id', $surveyIds)
    ->whereBetween('started_at', [$from, $to])
    ->selectRaw('device, count(*) as count')
    ->groupBy('device')
    ->pluck('count', 'device');

$sources = Response::query()
    ->whereIn('survey_id', $surveyIds)
    ->whereBetween('started_at', [$from, $to])
    ->selectRaw('source, count(*) as count')
    ->groupBy('source')
    ->pluck('count', 'source');
```

**Visualization**: Three donut charts side by side.

---

## 4. Geo Analytics

**What**: Country and city breakdown of respondents.

**Implementation**:
1. Add `country` and `city` columns to `responses`.
2. On response creation, resolve IP to geo using `ip-api.com` (free, no key required) or MaxMind GeoLite2.
3. Fire a queued job `ResolveResponseGeoJob` to avoid blocking the response submission.

```php
// In ResponseService::startOrResume()
ResolveResponseGeoJob::dispatch($response->id, $request->ip());
```

**Visualization**: Bar chart of top 10 countries; optional map widget.

---

## 5. Time-of-Day / Day-of-Week Heatmap

**What**: Show when respondents are most active.

**Implementation**:
```php
$hourly = Response::query()
    ->whereIn('survey_id', $surveyIds)
    ->selectRaw('HOUR(started_at) as hour, count(*) as count')
    ->groupBy('hour')
    ->pluck('count', 'hour');
```

**Visualization**: 24-column bar chart or heatmap grid.

---

## 6. Completion Rate Trend

**What**: Show completion rate over time (not just total).

**Implementation**:
```php
// Per day: completed / started ratio
$trend = Response::query()
    ->whereIn('survey_id', $surveyIds)
    ->whereBetween('started_at', [$from, $to])
    ->selectRaw('DATE(started_at) as day, 
        SUM(status = "completed") as completed,
        COUNT(*) as total')
    ->groupBy('day')
    ->get();
```

---

## 7. Average Completion Time Trend

**What**: Show how avg completion time changes over the date range.

**Implementation**: Same as trend but compute avg seconds per day.

---

## 8. NPS Trend Over Time

**What**: Show NPS score trend (weekly/monthly) to track improvement.

**Implementation**:
```php
// Group NPS scores by week, compute NPS per week
$npsWeekly = ResponseAnswer::query()
    ->where('question_id', $primaryQuestionId)
    ->whereHas('response', fn($q) => $q->whereBetween('started_at', [$from, $to]))
    ->selectRaw('YEARWEEK(responses.started_at) as week, AVG(score) as avg_score')
    ->join('responses', 'response_answers.response_id', '=', 'responses.id')
    ->groupBy('week')
    ->get();
```

---

## 9. Question-Level Response Rate

**What**: Show what % of respondents answered each question (identifies skipped optional questions).

**Implementation**:
```php
$totalCompleted = Response::where('survey_id', $survey->id)->where('status', 'completed')->count();

foreach ($survey->questions as $question) {
    $answered = ResponseAnswer::where('question_id', $question->id)
        ->whereHas('response', fn($q) => $q->where('status', 'completed'))
        ->count();
    $rate = $totalCompleted > 0 ? round($answered / $totalCompleted * 100, 1) : 0;
}
```

---

## 10. AI Response Summary

**What**: One-click AI-generated summary of all text responses for a survey.

**Implementation**:
```php
// Collect all text answers
$textAnswers = ResponseAnswer::query()
    ->whereHas('question', fn($q) => $q->whereHas('questionType', fn($qt) => $qt->whereIn('key', ['textbox', 'textarea'])))
    ->whereHas('response', fn($q) => $q->where('survey_id', $survey->id)->where('status', 'completed'))
    ->pluck('answer')
    ->filter()
    ->take(200); // limit tokens

// Send to OpenAI
$summary = OpenAI::chat()->create([
    'model' => 'gpt-4o-mini',
    'messages' => [
        ['role' => 'system', 'content' => 'Summarize these survey responses in 3-5 bullet points.'],
        ['role' => 'user', 'content' => $textAnswers->implode("\n")],
    ],
]);
```

Store result in `ai_summaries` table with TTL.

---

## 11. Sentiment Analysis (NLP)

**What**: Classify text answers as positive/neutral/negative using AI instead of score buckets.

**Implementation**:
```php
// Per text answer, call OpenAI with sentiment classification prompt
// Cache result in response_answers.sentiment column (add column)
```

---

## 12. Executive Report (Enhanced PDF)

**What**: Rich PDF report with charts, NPS score, top responses, recommendations.

**Current state**: Basic PDF exists via DomPDF.

**Improvements**:
1. Add NPS gauge chart (SVG-based, DomPDF-compatible).
2. Add sentiment breakdown pie chart.
3. Add top 5 positive and negative verbatim responses.
4. Add AI-generated recommendations section.
5. Add client logo and branding.

---

## Analytics Dashboard Layout Recommendation

```
┌─────────────────────────────────────────────────────────┐
│  [Survey Filter] [Date Range] [Export] [Live ●]         │
├──────────┬──────────┬──────────┬──────────┬─────────────┤
│ Total    │ Today    │ Compl.   │ Avg Time │ NPS Score   │
│ Responses│ Responses│ Rate     │          │             │
├──────────┴──────────┴──────────┴──────────┴─────────────┤
│  Response Trend (line chart)                            │
├─────────────────────┬───────────────────────────────────┤
│  Sentiment Donut    │  Device / Source / Browser Donuts │
├─────────────────────┴───────────────────────────────────┤
│  Question Breakdown (bar charts per question)           │
├─────────────────────────────────────────────────────────┤
│  Drop-off Funnel                                        │
├─────────────────────────────────────────────────────────┤
│  Review Click Breakdown                                 │
├─────────────────────────────────────────────────────────┤
│  Recent Responses Table                                 │
└─────────────────────────────────────────────────────────┘
```
