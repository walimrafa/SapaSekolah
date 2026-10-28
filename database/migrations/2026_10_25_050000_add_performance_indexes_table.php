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
        Schema::table('school_event_proposals', function (Blueprint $table) {
            $table->index('status');
            $table->index('execution_status');
            $table->index('event_date');
        });

        Schema::table('e_brochures', function (Blueprint $table) {
            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_event_proposals', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['execution_status']);
            $table->dropIndex(['event_date']);
        });

        Schema::table('e_brochures', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropIndex(['is_active']);
        });
    }
};
