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
            $table->string('followup_status', 30)->default('belum_dihubungi')->after('scholarship_interest');
            $table->timestamp('last_followup_at')->nullable()->after('followup_status');
            $table->foreignId('last_followed_up_by')->nullable()->after('last_followup_at')->constrained('users')->nullOnDelete();
            $table->text('followup_notes')->nullable()->after('last_followed_up_by');
            $table->text('college_interest_notes')->nullable()->after('followup_notes');
            $table->text('scholarship_notes')->nullable()->after('college_interest_notes');

            $table->index('followup_status');
            $table->index('scholarship_interest');
        });

        Schema::create('student_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_questionnaire_id')->constrained('student_questionnaires')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('followup_date');
            $table->string('contact_method', 30)->default('wa'); // wa, telepon, email, tatap_muka, lainnya
            $table->string('status', 30); // no_respon, respon, berminat, closing
            $table->text('scholarship_interest_notes')->nullable();
            $table->text('college_interest_notes')->nullable();
            $table->text('notes')->nullable();
            $table->date('next_followup_date')->nullable();
            $table->timestamps();

            $table->index('student_questionnaire_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('followup_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_follow_ups');

        Schema::table('student_questionnaires', function (Blueprint $table) {
            $table->dropForeign(['last_followed_up_by']);
            $table->dropIndex(['followup_status']);
            $table->dropIndex(['scholarship_interest']);
            $table->dropColumn([
                'followup_status',
                'last_followup_at',
                'last_followed_up_by',
                'followup_notes',
                'college_interest_notes',
                'scholarship_notes',
            ]);
        });
    }
};
