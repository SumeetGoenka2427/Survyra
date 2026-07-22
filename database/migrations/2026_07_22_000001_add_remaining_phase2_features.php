<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // TOTP for admin users
        Schema::table('users', function (Blueprint $table) {
            $table->string('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_recovery_codes');
        });

        // TOTP for client users
        Schema::table('client_users', function (Blueprint $table) {
            $table->string('two_factor_secret')->nullable()->after('password');
            $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_secret');
            $table->boolean('two_factor_enabled')->default(false)->after('two_factor_recovery_codes');
        });

        // Multi-language support for surveys
        Schema::table('surveys', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('settings');
            $table->string('default_locale', 10)->default('en')->after('translations');
        });

        // Survey question translations
        Schema::table('survey_questions', function (Blueprint $table) {
            $table->json('translations')->nullable()->after('settings');
        });

        // Onboarding checklist for clients
        Schema::create('onboarding_checklists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->boolean('profile_completed')->default(false);
            $table->boolean('first_survey_created')->default(false);
            $table->boolean('first_survey_published')->default(false);
            $table->boolean('first_campaign_sent')->default(false);
            $table->boolean('theme_customized')->default(false);
            $table->boolean('integrations_configured')->default(false);
            $table->boolean('dismissed')->default(false);
            $table->timestamps();
        });

        // Slack integration settings
        Schema::create('slack_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('webhook_url', 500);
            $table->string('channel')->nullable();
            $table->json('events')->default('["negative_feedback"]');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // File uploads for survey responses
        Schema::create('response_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained('responses')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('survey_questions')->cascadeOnDelete();
            $table->string('original_name');
            $table->string('stored_path');
            $table->string('mime_type', 100);
            $table->unsignedInteger('file_size');
            $table->timestamps();
        });

        // AI-generated content cache
        Schema::create('ai_content_cache', function (Blueprint $table) {
            $table->id();
            $table->morphs('ai_related');
            $table->string('type', 50); // survey_generator, question_suggestion, response_summary, etc.
            $table->json('input_context');
            $table->json('output_content');
            $table->unsignedSmallInteger('token_count')->nullable();
            $table->timestamps();
            $table->index(['ai_related_type', 'ai_related_id', 'type']);
        });

        // Zapier/Sheets integration tokens
        Schema::create('external_integrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->string('service', 50); // zapier, google_sheets
            $table->json('config');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
        });

        // Answer piping configuration
        Schema::table('survey_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('pipe_from_question_id')->nullable()->after('settings');
            $table->foreign('pipe_from_question_id')->references('id')->on('survey_questions')->nullOnDelete();
        });

        // NLP analysis results
        Schema::create('nlp_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50); // sentiment, keyword, summary
            $table->json('results');
            $table->timestamp('analyzed_at');
            $table->index(['client_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_enabled']);
        });
        Schema::table('client_users', function (Blueprint $table) {
            $table->dropColumn(['two_factor_secret', 'two_factor_recovery_codes', 'two_factor_enabled']);
        });
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn(['translations', 'default_locale']);
        });
        Schema::table('survey_questions', function (Blueprint $table) {
            $table->dropForeign(['pipe_from_question_id']);
            $table->dropColumn(['translations', 'pipe_from_question_id']);
        });
        Schema::dropIfExists('onboarding_checklists');
        Schema::dropIfExists('slack_integrations');
        Schema::dropIfExists('response_uploads');
        Schema::dropIfExists('ai_content_cache');
        Schema::dropIfExists('external_integrations');
        Schema::dropIfExists('nlp_analyses');
    }
};