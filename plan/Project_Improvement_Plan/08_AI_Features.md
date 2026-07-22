# 08 – AI Features

> Practical AI features for SMB survey platform using OpenAI API (GPT-4o-mini for cost efficiency).

---

## Architecture

All AI features should:
1. Use a queued job to avoid blocking HTTP requests.
2. Cache results in the database with a `generated_at` timestamp.
3. Allow manual regeneration.
4. Fail gracefully — if AI is unavailable, show cached result or "Not available".
5. Be gated behind a plan feature flag (`ai_features_enabled` on `subscription_plans`).

---

## 1. AI Survey Generator

**What**: Generate a complete survey (title + questions) from a text prompt.

**UI**: "Generate with AI" button on survey creation page. Text input: "Create a customer satisfaction survey for a dental clinic."

**Implementation**:
```php
// AiSurveyGeneratorService
public function generate(string $prompt, string $industry): array
{
    $response = OpenAI::chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'You are a survey design expert. Return JSON only.'],
            ['role' => 'user', 'content' => "Create a survey for: {$prompt}. Industry: {$industry}. 
             Return JSON: {title, questions: [{text, type, options?, required}]}
             Types: nps, csat, ces, radio, checkbox, textarea, rating_stars, yes_no"],
        ],
        'response_format' => ['type' => 'json_object'],
    ]);
    
    return json_decode($response->choices[0]->message->content, true);
}
```

**Flow**: Generate → Preview questions → Edit → Save as survey.

**Business value**: Reduces survey creation time from 30 minutes to 30 seconds.

---

## 2. AI Question Suggestions

**What**: While building a survey, suggest additional questions based on existing questions and industry.

**UI**: "Suggest questions" button in survey builder. Shows 3-5 suggestions with "Add" button per suggestion.

**Implementation**:
```php
public function suggestQuestions(Survey $survey): array
{
    $existing = $survey->questions->pluck('question_text')->implode(', ');
    
    $response = OpenAI::chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'Suggest 5 additional survey questions. Return JSON array.'],
            ['role' => 'user', 'content' => "Survey: {$survey->title}. Existing questions: {$existing}"],
        ],
        'response_format' => ['type' => 'json_object'],
    ]);
    
    return json_decode($response->choices[0]->message->content, true)['questions'];
}
```

---

## 3. Survey Quality Score

**What**: Analyze a survey and give it a quality score (0-100) with specific improvement tips.

**Checks**:
- Too many questions (>15 = fatigue risk)
- No NPS/CSAT/CES question (no measurable metric)
- All required questions (no optional = feels like interrogation)
- No open-ended question (no qualitative data)
- Question clarity (AI-assessed)

**UI**: "Check survey quality" button on survey edit page. Shows score + tips.

**Implementation**:
```php
public function scoresurvey(Survey $survey): array
{
    // Rule-based checks first (fast, no API call)
    $issues = [];
    $score = 100;
    
    if ($survey->questions->count() > 15) {
        $issues[] = 'Too many questions — consider splitting into multiple surveys.';
        $score -= 20;
    }
    
    // AI-based clarity check
    $questions = $survey->questions->pluck('question_text')->implode("\n");
    $aiResponse = OpenAI::chat()->create([...]);
    
    return ['score' => $score, 'issues' => $issues, 'ai_tips' => $aiTips];
}
```

---

## 4. AI Response Summary

**What**: Summarize all text responses for a survey into key themes and insights.

**UI**: "Generate AI Summary" button on analytics page. Shows summary in a card with "Regenerate" option.

**Implementation**:
```php
// Dispatched as a queued job: GenerateAiSummaryJob
public function handle(): void
{
    $answers = ResponseAnswer::query()
        ->whereHas('question', fn($q) => $q->whereHas('questionType', 
            fn($qt) => $qt->whereIn('key', ['textbox', 'textarea'])))
        ->whereHas('response', fn($q) => $q->where('survey_id', $this->surveyId)
            ->where('status', 'completed'))
        ->pluck('answer')
        ->filter()
        ->map(fn($a) => is_array($a) ? implode(', ', $a) : $a)
        ->take(300);

    if ($answers->isEmpty()) return;

    $response = OpenAI::chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'Analyze these survey responses. Return JSON with: 
             summary (2-3 sentences), key_themes (array of strings), 
             top_positives (array), top_negatives (array), recommendations (array).'],
            ['role' => 'user', 'content' => $answers->implode("\n---\n")],
        ],
        'response_format' => ['type' => 'json_object'],
    ]);

    AiSummary::updateOrCreate(
        ['survey_id' => $this->surveyId, 'type' => 'response_summary'],
        ['content' => $response->choices[0]->message->content, 'generated_at' => now()]
    );
}
```

---

## 5. Sentiment Analysis (NLP on Text Answers)

**What**: Classify each text answer as positive/neutral/negative using AI.

**Implementation**:
```php
// Batch classify up to 20 answers per API call to reduce costs
public function classifyBatch(array $texts): array
{
    $response = OpenAI::chat()->create([
        'model' => 'gpt-4o-mini',
        'messages' => [
            ['role' => 'system', 'content' => 'Classify each text as positive, neutral, or negative. 
             Return JSON array of sentiments in same order.'],
            ['role' => 'user', 'content' => json_encode($texts)],
        ],
        'response_format' => ['type' => 'json_object'],
    ]);
    
    return json_decode($response->choices[0]->message->content, true)['sentiments'];
}
```

---

## 6. Keyword Extraction

**What**: Extract the most common keywords/topics from text responses.

**Implementation**:
```php
// Part of GenerateAiSummaryJob — extract keywords alongside summary
// Store in ai_summaries with type='keywords'
// Display as tag cloud on analytics page
```

---

## 7. Recommended Actions

**What**: Based on NPS score, sentiment, and text analysis, suggest specific actions for the client.

**Examples**:
- "Your NPS dropped 15 points this month. Top complaint: 'waiting time'. Consider reviewing your appointment scheduling."
- "87% of positive responses mention 'staff friendliness'. Highlight this in your marketing."

**Implementation**:
```php
// Part of AI summary generation
// Include metrics context in the prompt
$prompt = "NPS: {$nps}, CSAT: {$csat}, Top themes: {$themes}. 
           Suggest 3 specific business actions the client should take.";
```

---

## 8. Duplicate Question Detection

**What**: Warn when a new question is semantically similar to an existing one.

**Implementation**:
```php
// On question save, check similarity against existing questions
// Use OpenAI embeddings or simple string similarity for MVP
public function isDuplicate(string $newQuestion, Collection $existingQuestions): bool
{
    foreach ($existingQuestions as $q) {
        similar_text(strtolower($newQuestion), strtolower($q->question_text), $percent);
        if ($percent > 80) return true;
    }
    return false;
}
```

---

## 9. AI Dashboard Widget

**What**: A dedicated "AI Insights" card on the analytics dashboard showing:
- Latest AI summary (with date)
- Top 3 keywords
- Top recommendation
- "Generate / Regenerate" button

---

## 10. Natural Language Report

**What**: Generate a narrative report in plain English that a non-technical client can share with their team.

**Example output**:
> "This month, 142 customers completed your satisfaction survey. Your NPS score is 67, which is above the industry average of 45 for dental clinics. The most common positive theme is 'friendly staff' (mentioned in 68% of responses). The main area for improvement is 'waiting time' (mentioned in 34% of negative responses). We recommend reviewing your appointment scheduling process."

**Implementation**: Combine all metrics + AI summary into a structured prompt.

---

## Cost Estimation (OpenAI GPT-4o-mini)

| Feature | Tokens per call | Cost per call | Calls/month (100 clients) |
|---|---|---|---|
| Survey Generator | ~500 | ~$0.0003 | 500 |
| Response Summary | ~2000 | ~$0.0012 | 1000 |
| Question Suggestions | ~300 | ~$0.0002 | 2000 |
| Sentiment (batch 20) | ~400 | ~$0.0002 | 5000 |
| **Total/month** | | | **~$5–15** |

AI features are extremely cost-effective at SMB scale.

---

## Required Config

```env
OPENAI_API_KEY=sk-...
OPENAI_MODEL=gpt-4o-mini
AI_FEATURES_ENABLED=true
```

```php
// config/ai.php
return [
    'enabled' => env('AI_FEATURES_ENABLED', false),
    'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    'max_summary_responses' => 300,
];
```
