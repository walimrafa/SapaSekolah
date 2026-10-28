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
        Schema::table('articles', function (Blueprint $table) {
            $table->string('event_name', 255)->nullable()->after('event_category');
            $table->string('event_theme', 255)->nullable()->after('event_name');
            $table->string('event_date', 255)->nullable()->after('event_theme');
            $table->string('event_time', 255)->nullable()->after('event_date');
            $table->string('event_location', 255)->nullable()->after('event_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn([
                'event_name',
                'event_theme',
                'event_date',
                'event_time',
                'event_location',
            ]);
        });
    }
};
