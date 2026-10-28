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
        Schema::table('student_questionnaires', function (Blueprint $table) {
            $table->foreignId('school_event_proposal_id')->nullable()->after('school_id')->constrained('school_event_proposals')->nullOnDelete();
            $table->string('source', 50)->default('kuisioner')->after('scholarship_interest');

            $table->index('source');
            $table->index('school_event_proposal_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('student_questionnaires', function (Blueprint $table) {
            $table->dropForeign(['school_event_proposal_id']);
            $table->dropIndex(['source']);
            $table->dropIndex(['school_event_proposal_id']);
            $table->dropColumn(['school_event_proposal_id', 'source']);
        });
    }
};
