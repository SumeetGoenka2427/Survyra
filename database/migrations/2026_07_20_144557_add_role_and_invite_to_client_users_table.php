<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_users', function (Blueprint $table) {
            $table->string('role')->default('editor')->change(); // owner | editor | viewer
            $table->unsignedBigInteger('invited_by')->nullable()->after('role');
            $table->string('invitation_token', 64)->nullable()->unique()->after('invited_by');
            $table->timestamp('invitation_accepted_at')->nullable()->after('invitation_token');
        });
    }

    public function down(): void
    {
        Schema::table('client_users', function (Blueprint $table) {
            $table->dropColumn(['invited_by', 'invitation_token', 'invitation_accepted_at']);
        });
    }
};
