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
        Schema::table('prospects', function (Blueprint $table) {
            if (Schema::hasColumn('prospects', 'student_class')) {
                $table->index('student_class');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prospects', function (Blueprint $table) {
            if (Schema::hasColumn('prospects', 'student_class')) {
                $table->dropIndex(['student_class']);
            }
        });
    }
};

