<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->date('socialization_completed_at')->nullable()->after('target_audience_count');
            $table->text('socialization_completion_notes')->nullable()->after('socialization_completed_at');
            $table->index('socialization_completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropIndex(['socialization_completed_at']);
            $table->dropColumn(['socialization_completed_at', 'socialization_completion_notes']);
        });
    }
};
