<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('survey_thankyou_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('sentiment');
            $table->integer('min_score')->nullable();
            $table->integer('max_score')->nullable();
            $table->text('thank_you_message')->nullable();
            $table->boolean('show_google_review')->default(false);
            $table->boolean('show_facebook')->default(false);
            $table->boolean('show_instagram')->default(false);
            $table->boolean('show_website')->default(false);
            $table->boolean('show_coupon')->default(false);
            $table->string('coupon_code')->nullable();
            $table->boolean('show_complaint_form')->default(false);
            $table->boolean('show_support_number')->default(false);
            $table->boolean('show_whatsapp_button')->default(false);
            $table->json('manager_contact')->nullable();
            $table->timestamps();

            $table->unique(['survey_id', 'sentiment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_thankyou_rules');
    }
};
