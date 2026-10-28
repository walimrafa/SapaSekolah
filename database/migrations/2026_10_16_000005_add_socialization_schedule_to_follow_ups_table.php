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
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->string('status', 50)->default('pending')->change();
            $table->string('method', 50)->default('wa')->change();

            $table->date('fixed_date')->nullable()->after('next_followup_date');
            $table->string('fixed_time', 50)->nullable()->after('fixed_date');
            $table->string('pic_name', 150)->nullable()->after('fixed_time');
            $table->string('pic_phone', 50)->nullable()->after('pic_name');
            $table->string('pic_position', 100)->nullable()->after('pic_phone');
            $table->string('socialization_location', 150)->nullable()->after('pic_position');
            $table->boolean('is_schedule_fixed')->default(false)->after('socialization_location');
            $table->integer('target_audience_count')->nullable()->after('is_schedule_fixed');
            $table->text('notes')->nullable()->after('description');

            $table->index('is_schedule_fixed');
            $table->index('fixed_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('follow_ups', function (Blueprint $table) {
            $table->dropIndex(['is_schedule_fixed']);
            $table->dropIndex(['fixed_date']);
            $table->dropColumn([
                'fixed_date',
                'fixed_time',
                'pic_name',
                'pic_phone',
                'pic_position',
                'socialization_location',
                'is_schedule_fixed',
                'target_audience_count',
                'notes',
            ]);
        });
    }
};
