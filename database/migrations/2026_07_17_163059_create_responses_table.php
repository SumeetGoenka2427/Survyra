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
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('respondent_identifier')->nullable();
            $table->string('status')->default('started');
            $table->string('device')->nullable();
            $table->string('browser')->nullable();
            $table->string('ip')->nullable();
            $table->string('source')->default('direct');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->string('sentiment')->nullable();
            $table->timestamps();

            $table->index(['survey_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('responses');
    }
};
