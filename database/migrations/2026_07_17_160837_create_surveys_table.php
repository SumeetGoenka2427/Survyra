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
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_template_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('theme_id')->nullable()->constrained('survey_themes')->nullOnDelete();
            $table->string('title');
            $table->string('slug')->nullable()->unique();
            $table->unsignedInteger('version')->default(1);
            $table->string('status')->default('draft');
            $table->json('settings')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('surveys');
    }
};
