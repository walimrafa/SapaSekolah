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
            $table->string('recipient_name', 150)->nullable()->after('contact_person');
            $table->string('recipient_position', 100)->nullable()->after('recipient_name');
            $table->integer('grade_12_class_count')->nullable()->after('recipient_position');
            $table->integer('students_per_class')->nullable()->after('grade_12_class_count');
            $table->string('target_month_year', 50)->nullable()->after('students_per_class');
            $table->date('target_date')->nullable()->after('target_month_year');
            $table->string('target_time', 20)->nullable()->after('target_date');
            $table->string('socialization_method', 100)->nullable()->after('target_time');
            $table->string('other_activity_name', 200)->nullable()->after('socialization_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('school_visits', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_name',
                'recipient_position',
                'grade_12_class_count',
                'students_per_class',
                'target_month_year',
                'target_date',
                'target_time',
                'socialization_method',
                'other_activity_name',
            ]);
        });
    }
};
