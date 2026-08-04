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
        Schema::create('website_themes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_system')->default(false);
            $table->foreignId('client_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('primary_color')->default('#0d6efd');
            $table->string('secondary_color')->default('#6c757d');
            $table->string('background')->default('#ffffff');
            $table->string('heading_font')->default('system-ui');
            $table->string('body_font')->default('system-ui');
            $table->string('header_style')->default('centered');
            $table->string('button_style')->default('rounded');
            $table->unsignedSmallInteger('border_radius')->default(8);
            $table->string('container_width')->default('boxed');
            $table->text('custom_css')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('website_themes');
    }
};
