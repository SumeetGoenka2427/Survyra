# 05 – Database Changes

> All required schema changes grouped by feature. Each entry includes migration details, indexes, and rationale.

---

## Surveys Table

```sql
-- Welcome screen / intro page
ALTER TABLE surveys ADD COLUMN welcome_screen JSON NULL;

-- Expiry and response cap
ALTER TABLE surveys ADD COLUMN expires_at TIMESTAMP NULL;
ALTER TABLE surveys ADD COLUMN max_responses INT UNSIGNED NULL;

-- Password protection
ALTER TABLE surveys ADD COLUMN password VARCHAR(255) NULL;

-- Anonymous mode
ALTER TABLE surveys ADD COLUMN is_anonymous BOOLEAN NOT NULL DEFAULT FALSE;

-- GDPR consent toggle
ALTER TABLE surveys ADD COLUMN gdpr_enabled BOOLEAN NOT NULL DEFAULT FALSE;
ALTER TABLE surveys ADD COLUMN gdpr_text TEXT NULL;

-- Tracking pixels
ALTER TABLE surveys ADD COLUMN ga_tracking_id VARCHAR(100) NULL;
ALTER TABLE surveys ADD COLUMN meta_pixel_id VARCHAR(100) NULL;

-- Indexes
CREATE INDEX idx_surveys_client_id ON surveys(client_id);
CREATE INDEX idx_surveys_status ON surveys(status);
CREATE INDEX idx_surveys_slug ON surveys(slug);
```

## Survey Logic Rules Table

```sql
-- OR condition support
ALTER TABLE survey_logic_rules ADD COLUMN condition_operator ENUM('AND','OR') NOT NULL DEFAULT 'AND';

-- Jump-to-page / end-survey actions
-- action column already exists; extend ENUM to include 'jump_to_question', 'end_survey'
```

## Responses Table

```sql
-- Drop-off tracking
ALTER TABLE responses ADD COLUMN last_question_id INT UNSIGNED NULL;
ALTER TABLE responses ADD COLUMN drop_off_at TIMESTAMP NULL;

-- Geo analytics
ALTER TABLE responses ADD COLUMN country VARCHAR(100) NULL;
ALTER TABLE responses ADD COLUMN city VARCHAR(100) NULL;

-- Indexes (critical for analytics performance)
CREATE INDEX idx_responses_survey_id ON responses(survey_id);
CREATE INDEX idx_responses_client_id ON responses(client_id);
CREATE INDEX idx_responses_status ON responses(status);
CREATE INDEX idx_responses_started_at ON responses(started_at);
CREATE INDEX idx_responses_campaign_id ON responses(campaign_id);
CREATE INDEX idx_responses_contact_id ON responses(contact_id);
```

## Response Answers Table

```sql
CREATE INDEX idx_response_answers_response_id ON response_answers(response_id);
CREATE INDEX idx_response_answers_question_id ON response_answers(question_id);
```

## Campaign Recipients Table

```sql
CREATE INDEX idx_campaign_recipients_campaign_id ON campaign_recipients(campaign_id);
CREATE INDEX idx_campaign_recipients_status ON campaign_recipients(status);
CREATE INDEX idx_campaign_recipients_contact_id ON campaign_recipients(contact_id);
```

## New: API Keys Table

```sql
CREATE TABLE api_keys (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    key_hash VARCHAR(255) NOT NULL UNIQUE,
    last_used_at TIMESTAMP NULL,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_api_keys_client_id (client_id),
    INDEX idx_api_keys_key_hash (key_hash)
);
```

## New: Webhooks Table

```sql
CREATE TABLE webhooks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    survey_id BIGINT UNSIGNED NULL,
    url VARCHAR(500) NOT NULL,
    events JSON NOT NULL,  -- ['response.completed', 'response.started']
    secret VARCHAR(255) NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    last_triggered_at TIMESTAMP NULL,
    failure_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    INDEX idx_webhooks_client_id (client_id)
);
```

## New: Webhook Deliveries Table

```sql
CREATE TABLE webhook_deliveries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    webhook_id BIGINT UNSIGNED NOT NULL,
    event VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    response_status INT NULL,
    response_body TEXT NULL,
    delivered_at TIMESTAMP NULL,
    failed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (webhook_id) REFERENCES webhooks(id) ON DELETE CASCADE
);
```

## New: Client Users Table (extend existing)

```sql
ALTER TABLE client_users ADD COLUMN role ENUM('owner','editor','viewer') NOT NULL DEFAULT 'editor';
ALTER TABLE client_users ADD COLUMN invited_by INT UNSIGNED NULL;
ALTER TABLE client_users ADD COLUMN invitation_token VARCHAR(255) NULL;
ALTER TABLE client_users ADD COLUMN invitation_accepted_at TIMESTAMP NULL;
```

## New: Subscription Usage Table

```sql
CREATE TABLE subscription_usages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    responses_count INT UNSIGNED NOT NULL DEFAULT 0,
    campaign_sends_count INT UNSIGNED NOT NULL DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    UNIQUE KEY uq_usage_client_period (client_id, period_start)
);
```

## New: Stripe Billing Tables

```sql
CREATE TABLE subscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL UNIQUE,
    stripe_customer_id VARCHAR(255) NULL,
    stripe_subscription_id VARCHAR(255) NULL,
    stripe_price_id VARCHAR(255) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'inactive',
    trial_ends_at TIMESTAMP NULL,
    current_period_start TIMESTAMP NULL,
    current_period_end TIMESTAMP NULL,
    canceled_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE
);
```

## Subscription Plans Table (extend)

```sql
ALTER TABLE subscription_plans ADD COLUMN stripe_price_id VARCHAR(255) NULL;
ALTER TABLE subscription_plans ADD COLUMN features JSON NULL;
ALTER TABLE subscription_plans ADD COLUMN sort_order INT UNSIGNED NOT NULL DEFAULT 0;
```

## New: AI Summaries Table

```sql
CREATE TABLE ai_summaries (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    survey_id BIGINT UNSIGNED NOT NULL,
    type ENUM('response_summary','sentiment','keywords','recommendations') NOT NULL,
    content TEXT NOT NULL,
    generated_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    FOREIGN KEY (survey_id) REFERENCES surveys(id) ON DELETE CASCADE,
    INDEX idx_ai_summaries_survey_id (survey_id)
);
```

---

## Migration File Naming Convention

```
2026_XX_XX_XXXXXX_add_welcome_screen_to_surveys_table.php
2026_XX_XX_XXXXXX_add_expiry_and_cap_to_surveys_table.php
2026_XX_XX_XXXXXX_add_security_columns_to_surveys_table.php
2026_XX_XX_XXXXXX_add_drop_off_tracking_to_responses_table.php
2026_XX_XX_XXXXXX_add_geo_columns_to_responses_table.php
2026_XX_XX_XXXXXX_add_performance_indexes.php
2026_XX_XX_XXXXXX_create_api_keys_table.php
2026_XX_XX_XXXXXX_create_webhooks_table.php
2026_XX_XX_XXXXXX_create_webhook_deliveries_table.php
2026_XX_XX_XXXXXX_add_role_to_client_users_table.php
2026_XX_XX_XXXXXX_create_subscription_usages_table.php
2026_XX_XX_XXXXXX_create_subscriptions_table.php
2026_XX_XX_XXXXXX_add_stripe_to_subscription_plans_table.php
2026_XX_XX_XXXXXX_create_ai_summaries_table.php
```
