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
        Schema::table('survey_templates', function (Blueprint $table) {
            $table->string('layout')->default('multi_step')->after('description');
        });

        Schema::table('surveys', function (Blueprint $table) {
            $table->string('layout')->default('multi_step')->after('version');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_templates', function (Blueprint $table) {
            $table->dropColumn('layout');
        });

        Schema::table('surveys', function (Blueprint $table) {
            $table->dropColumn('layout');
        });
    }
};
