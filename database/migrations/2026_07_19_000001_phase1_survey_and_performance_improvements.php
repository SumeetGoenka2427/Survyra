<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Surveys: blank creation, expiry, response cap, welcome screen, anonymous, GDPR, tracking
        Schema::table('surveys', function (Blueprint $table) {
            $table->json('welcome_screen')->nullable()->after('settings');
            $table->timestamp('expires_at')->nullable()->after('welcome_screen');
            $table->unsignedInteger('max_responses')->nullable()->after('expires_at');
            $table->boolean('is_anonymous')->default(false)->after('max_responses');
            $table->boolean('gdpr_enabled')->default(false)->after('is_anonymous');
            $table->text('gdpr_text')->nullable()->after('gdpr_enabled');
            $table->string('privacy_policy_url')->nullable()->after('gdpr_text');
            $table->string('ga_tracking_id')->nullable()->after('privacy_policy_url');
            $table->string('meta_pixel_id')->nullable()->after('ga_tracking_id');

            $table->index(['client_id', 'status']);
        });

        // Responses: drop-off tracking, geo
        Schema::table('responses', function (Blueprint $table) {
            $table->foreignId('last_question_id')->nullable()->constrained('survey_questions')->nullOnDelete()->after('sentiment');
            $table->timestamp('drop_off_at')->nullable()->after('last_question_id');
            $table->string('country', 100)->nullable()->after('drop_off_at');
            $table->string('city', 100)->nullable()->after('country');

            $table->index(['client_id', 'started_at']);
            $table->index('campaign_id');
            $table->index('contact_id');
            $table->index('started_at');
        });

        // Response answers
        Schema::table('response_answers', function (Blueprint $table) {
            $table->index('response_id');
            $table->index('question_id');
        });

        // Campaign recipients
        Schema::table('campaign_recipients', function (Blueprint $table) {
            $table->index(['campaign_id', 'status']);
            $table->index('contact_id');
        });

        // Short links
        Schema::table('short_links', function (Blueprint $table) {
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn(['welcome_screen', 'expires_at', 'max_responses', 'is_anonymous', 'gdpr_enabled', 'gdpr_text', 'privacy_policy_url', 'ga_tracking_id', 'meta_pixel_id']);
            $table->dropIndex(['client_id', 'status']);
        });

        Schema::table('responses', function (Blueprint $table) {
            $table->dropForeign(['last_question_id']);
            $table->dropColumn(['last_question_id', 'drop_off_at', 'country', 'city']);
            $table->dropIndex(['client_id', 'started_at']);
            $table->dropIndex(['campaign_id']);
            $table->dropIndex(['contact_id']);
            $table->dropIndex(['started_at']);
        });

        Schema::table('response_answers', function (Blueprint $table) {
            $table->dropIndex(['response_id']);
            $table->dropIndex(['question_id']);
        });

        Schema::table('campaign_recipients', function (Blueprint $table) {
            $table->dropIndex(['campaign_id', 'status']);
            $table->dropIndex(['contact_id']);
        });

        Schema::table('short_links', function (Blueprint $table) {
            $table->dropIndex(['code']);
        });
    }
};
