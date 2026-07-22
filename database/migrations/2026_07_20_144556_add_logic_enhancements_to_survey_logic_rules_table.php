<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_logic_rules', function (Blueprint $table) {
            $table->string('condition_operator')->default('AND')->after('conditions'); // AND | OR
            // action already exists; we extend it to support jump_to_question, end_survey
        });
    }

    public function down(): void
    {
        Schema::table('survey_logic_rules', function (Blueprint $table) {
            $table->dropColumn('condition_operator');
        });
    }
};
