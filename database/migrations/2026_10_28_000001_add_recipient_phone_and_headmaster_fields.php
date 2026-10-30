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
        Schema::table('school_visits', function (Blueprint $table) {
            if (!Schema::hasColumn('school_visits', 'headmaster_name')) {
                $table->string('headmaster_name', 150)->nullable()->after('school_id');
            }
            if (!Schema::hasColumn('school_visits', 'recipient_phone')) {
                $table->string('recipient_phone', 30)->nullable()->after('recipient_position');
            }
        });

        Schema::table('schools', function (Blueprint $table) {
            if (!Schema::hasColumn('schools', 'headmaster_name')) {
                $table->string('headmaster_name', 150)->nullable()->after('name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_visits', function (Blueprint $table) {
            $table->dropColumn(['headmaster_name', 'recipient_phone']);
        });

        Schema::table('schools', function (Blueprint $table) {
            $table->dropColumn(['headmaster_name']);
        });
    }
};
