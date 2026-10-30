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
        Schema::table('marketing_activities', function (Blueprint $table) {
            $table->index('start_date');
            $table->index('activity_type');
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketing_activities', function (Blueprint $table) {
            $table->dropIndex(['start_date']);
            $table->dropIndex(['activity_type']);
            $table->dropIndex(['name']);
        });
    }
};
