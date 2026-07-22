# 11 – Scalability

> Architecture review and recommendations for scaling Survyra to handle growth.

---

## Current Architecture Assessment

| Component | Current State | Scalability Risk |
|---|---|---|
| Database | SQLite (dev) / MySQL (prod assumed) | High if SQLite used in prod |
| Queue | Laravel queue (sync/database driver assumed) | High — campaigns block if sync |
| Cache | No caching configured | High — analytics queries hit DB every request |
| Storage | Local filesystem | High — not portable, no CDN |
| Search | No search beyond basic SQL LIKE | Medium |
| Sessions | File/database | Medium |
| Background jobs | `SendCampaignJob` + `SendCampaignMessageJob` | Medium — no monitoring |

---

## 1. Database Optimization

### Missing Indexes (Critical)

```php
// Migration: add_performance_indexes
Schema::table('responses', function (Blueprint $table) {
    $table->index(['survey_id', 'status']);
    $table->index(['client_id', 'started_at']);
    $table->index(['campaign_id']);
    $table->index(['contact_id']);
    $table->index('started_at');
});

Schema::table('response_answers', function (Blueprint $table) {
    $table->index('response_id');
    $table->index('question_id');
});

Schema::table('campaign_recipients', function (Blueprint $table) {
    $table->index(['campaign_id', 'status']);
    $table->index('contact_id');
});

Schema::table('surveys', function (Blueprint $table) {
    $table->index(['client_id', 'status']);
    $table->index('slug');
});

Schema::table('short_links', function (Blueprint $table) {
    $table->index('code');
});
```

### Query Optimization

```php
// AnalyticsService: avoid N+1 on question breakdown
$survey->load(['questions.questionType', 'questions.answers' => function($q) use ($from, $to) {
    $q->whereHas('response', fn($r) => $r->where('status', 'completed')
        ->whereBetween('started_at', [$from, $to]));
}]);

// Use chunking for large response exports
Response::where('survey_id', $surveyId)->chunk(500, function ($responses) use (&$rows) {
    foreach ($responses as $response) {
        $rows[] = $this->formatRow($response);
    }
});
```

### Database Connection Pooling

```php
// config/database.php — for MySQL in production
'mysql' => [
    'options' => [
        PDO::ATTR_PERSISTENT => true, // connection pooling
    ],
    'pool' => [
        'min' => 2,
        'max' => 10,
    ],
],
```

---

## 2. Redis Caching

### Setup

```env
CACHE_DRIVER=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
```

### Analytics Caching

```php
// Cache analytics results for 5 minutes
public function forClient(Client $client, ?Survey $survey, Carbon $from, Carbon $to): array
{
    $cacheKey = "analytics:{$client->id}:{$survey?->id}:{$from->toDateString()}:{$to->toDateString()}";
    
    return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($client, $survey, $from, $to) {
        return $this->computeAnalytics($client, $survey, $from, $to);
    });
}

// Invalidate on new response
Cache::tags(["client:{$response->client_id}"])->flush();
```

### Survey Caching

```php
// Cache published survey + questions for 10 minutes
// Invalidate on survey update
public function findBySlug(string $slug): Survey
{
    return Cache::remember("survey:slug:{$slug}", now()->addMinutes(10), function () use ($slug) {
        return Survey::with(['questions.questionType', 'logicRules', 'thankyouRules', 'theme', 'client'])
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();
    });
}
```

### Short Link Caching

```php
// Short links are read-heavy, write-rarely — cache aggressively
Cache::remember("short_link:{$code}", now()->addHour(), fn() => ShortLink::where('code', $code)->firstOrFail());
```

---

## 3. Queue Configuration

### Redis Queue (Required for Production)

```env
QUEUE_CONNECTION=redis
```

### Queue Workers

```bash
# Separate queues for different priorities
php artisan queue:work redis --queue=high,default,low --tries=3 --timeout=60

# Campaign sends on dedicated worker
php artisan queue:work redis --queue=campaigns --tries=3 --timeout=120
```

### Job Configuration

```php
// SendCampaignMessageJob — add retry and backoff
public function retryUntil(): DateTime
{
    return now()->addHours(2);
}

public function backoff(): array
{
    return [30, 60, 120]; // seconds between retries
}
```

---

## 4. Laravel Horizon (Queue Monitoring)

```bash
composer require laravel/horizon
php artisan horizon:install
```

```php
// config/horizon.php
'environments' => [
    'production' => [
        'supervisor-1' => [
            'connection' => 'redis',
            'queue' => ['high', 'default'],
            'balance' => 'auto',
            'minProcesses' => 1,
            'maxProcesses' => 10,
        ],
        'campaigns' => [
            'connection' => 'redis',
            'queue' => ['campaigns'],
            'balance' => 'simple',
            'processes' => 3,
        ],
    ],
],
```

---

## 5. S3 / Cloud Storage

```env
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=ap-south-1
AWS_BUCKET=survyra-storage
AWS_URL=https://survyra-storage.s3.ap-south-1.amazonaws.com
```

```php
// config/filesystems.php — already has S3 config, just needs env vars
// Update all Storage::put() calls to use the configured disk (already using Storage facade)

// For public files (logos, QR codes):
Storage::disk('s3')->put($path, $content, 'public');
$url = Storage::disk('s3')->url($path);
```

### CDN (CloudFront)

```env
AWS_URL=https://d1234567890.cloudfront.net
```

Configure CloudFront distribution pointing to S3 bucket. All public assets served via CDN.

---

## 6. Search

### Current State
- `SearchController` exists but implementation unknown.
- Likely uses SQL `LIKE` queries.

### Recommendation for Scale

```bash
# Option A: Laravel Scout + Meilisearch (self-hosted, free)
composer require laravel/scout
# Add Searchable trait to Survey, Client, Contact models

# Option B: Simple full-text search with MySQL FULLTEXT indexes
ALTER TABLE surveys ADD FULLTEXT INDEX ft_surveys_title (title);
ALTER TABLE contacts ADD FULLTEXT INDEX ft_contacts_name (name);
```

---

## 7. Background Jobs Architecture

### Current Jobs
- `SendCampaignJob` — dispatches per-recipient jobs
- `SendCampaignMessageJob` — sends one message

### Recommended Additions
```php
// ResolveResponseGeoJob — async IP geolocation
// DeliverWebhookJob — async webhook delivery with retry
// GenerateAiSummaryJob — async AI processing
// GenerateQrCodeJob — async QR generation for large batches
// SendScheduledReportJob — already exists via console command
// PurgeExpiredResponsesJob — data retention
```

---

## 8. Performance Monitoring

### Recommendations

```bash
# Laravel Telescope (dev/staging)
composer require laravel/telescope --dev

# Sentry (production error tracking)
composer require sentry/sentry-laravel

# New Relic or Datadog APM for production
```

```php
// Add slow query logging
DB::listen(function ($query) {
    if ($query->time > 1000) { // > 1 second
        Log::warning('Slow query', ['sql' => $query->sql, 'time' => $query->time]);
    }
});
```

---

## 9. Horizontal Scaling Readiness

For the platform to scale horizontally (multiple app servers):

| Requirement | Status | Action |
|---|---|---|
| Sessions in Redis | ❌ | Set `SESSION_DRIVER=redis` |
| Cache in Redis | ❌ | Set `CACHE_DRIVER=redis` |
| Queue in Redis | ❌ | Set `QUEUE_CONNECTION=redis` |
| Files in S3 | ❌ | Set `FILESYSTEM_DISK=s3` |
| No local state | ✅ | No local state found |
| UUID on responses | ✅ | Already implemented |

Once Redis and S3 are configured, the app is ready for horizontal scaling behind a load balancer.

---

## 10. Estimated Capacity (After Optimizations)

| Metric | Before | After (Redis + Indexes + S3) |
|---|---|---|
| Concurrent survey respondents | ~50 | ~500+ |
| Analytics page load time | 2-5s | <500ms (cached) |
| Campaign send throughput | ~100/min | ~1000/min (queue workers) |
| Storage capacity | Server disk | Unlimited (S3) |
| Uptime | Single server | Multi-server with load balancer |
