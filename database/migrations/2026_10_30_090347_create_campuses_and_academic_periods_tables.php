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
        // 1. Tabel Master Data Kampus
        if (!Schema::hasTable('campuses')) {
            Schema::create('campuses', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 150);
                $table->string('city', 100)->nullable();
                $table->text('address')->nullable();
                $table->decimal('latitude', 10, 8)->default(-6.3054);
                $table->decimal('longitude', 11, 8)->default(107.2995);
                $table->string('phone', 50)->nullable();
                $table->string('email', 100)->nullable();
                $table->string('website', 150)->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_default')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('code');
                $table->index('is_active');
                $table->index('is_default');
            });
        }

        // 2. Tabel Master Data Periode Akademik / PMB
        if (!Schema::hasTable('academic_periods')) {
            Schema::create('academic_periods', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50)->unique();
                $table->date('start_date')->nullable();
                $table->date('end_date')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_current')->default(false);
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->index('name');
                $table->index('is_active');
                $table->index('is_current');
            });
        }

        // 3. Relasi Kampus & Periode ke Tabel Sekolah & Operasional
        if (!Schema::hasColumn('schools', 'campus_id')) {
            Schema::table('schools', function (Blueprint $table) {
                $table->foreignId('campus_id')->nullable()->after('status_school')->constrained('campuses')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('school_visits', 'campus_id')) {
            Schema::table('school_visits', function (Blueprint $table) {
                $table->foreignId('campus_id')->nullable()->after('user_id')->constrained('campuses')->nullOnDelete();
                $table->foreignId('academic_period_id')->nullable()->after('campus_id')->constrained('academic_periods')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('visit_plans', 'campus_id')) {
            Schema::table('visit_plans', function (Blueprint $table) {
                $table->foreignId('campus_id')->nullable()->after('user_id')->constrained('campuses')->nullOnDelete();
                $table->foreignId('academic_period_id')->nullable()->after('campus_id')->constrained('academic_periods')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('marketing_routes', 'campus_id')) {
            Schema::table('marketing_routes', function (Blueprint $table) {
                $table->foreignId('campus_id')->nullable()->after('user_id')->constrained('campuses')->nullOnDelete();
                $table->foreignId('academic_period_id')->nullable()->after('campus_id')->constrained('academic_periods')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('follow_ups', 'campus_id')) {
            Schema::table('follow_ups', function (Blueprint $table) {
                $table->foreignId('campus_id')->nullable()->after('user_id')->constrained('campuses')->nullOnDelete();
                $table->foreignId('academic_period_id')->nullable()->after('campus_id')->constrained('academic_periods')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('prospects', 'campus_id')) {
            Schema::table('prospects', function (Blueprint $table) {
                $table->foreignId('campus_id')->nullable()->after('marketing_user_id')->constrained('campuses')->nullOnDelete();
                $table->foreignId('academic_period_id')->nullable()->after('campus_id')->constrained('academic_periods')->nullOnDelete();
                if (!Schema::hasColumn('prospects', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        if (!Schema::hasColumn('student_questionnaires', 'campus_id')) {
            Schema::table('student_questionnaires', function (Blueprint $table) {
                $table->foreignId('campus_id')->nullable()->after('assigned_at')->constrained('campuses')->nullOnDelete();
                $table->foreignId('academic_period_id')->nullable()->after('campus_id')->constrained('academic_periods')->nullOnDelete();
                if (!Schema::hasColumn('student_questionnaires', 'deleted_at')) {
                    $table->softDeletes()->after('updated_at');
                }
            });
        }

        Schema::table('school_event_proposals', function (Blueprint $table) {
            if (!Schema::hasColumn('school_event_proposals', 'campus_id')) {
                $table->foreignId('campus_id')->nullable()->after('school_id')->constrained('campuses')->nullOnDelete();
            }
            if (!Schema::hasColumn('school_event_proposals', 'academic_period_id')) {
                $table->foreignId('academic_period_id')->nullable()->after('campus_id')->constrained('academic_periods')->nullOnDelete();
            }
            if (!Schema::hasColumn('school_event_proposals', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'campus_id')) {
                $table->foreignId('campus_id')->nullable()->after('role_id')->constrained('campuses')->nullOnDelete();
            }
        });

        Schema::table('activity_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('activity_logs', 'description')) {
                $table->text('description')->nullable()->after('action');
            }
            if (!Schema::hasColumn('activity_logs', 'campus_id')) {
                $table->foreignId('campus_id')->nullable()->after('user_agent')->constrained('campuses')->nullOnDelete();
            }
            if (!Schema::hasColumn('activity_logs', 'academic_period_id')) {
                $table->foreignId('academic_period_id')->nullable()->after('campus_id')->constrained('academic_periods')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            if (Schema::hasColumn('activity_logs', 'campus_id')) $table->dropForeign(['campus_id']);
            if (Schema::hasColumn('activity_logs', 'academic_period_id')) $table->dropForeign(['academic_period_id']);
            $table->dropColumn(array_filter(['description', 'campus_id', 'academic_period_id'], fn($c) => Schema::hasColumn('activity_logs', $c)));
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'campus_id')) {
                $table->dropForeign(['campus_id']);
                $table->dropColumn('campus_id');
            }
        });

        Schema::table('school_event_proposals', function (Blueprint $table) {
            if (Schema::hasColumn('school_event_proposals', 'campus_id')) $table->dropForeign(['campus_id']);
            if (Schema::hasColumn('school_event_proposals', 'academic_period_id')) $table->dropForeign(['academic_period_id']);
            $table->dropColumn(array_filter(['campus_id', 'academic_period_id'], fn($c) => Schema::hasColumn('school_event_proposals', $c)));
        });

        Schema::table('student_questionnaires', function (Blueprint $table) {
            if (Schema::hasColumn('student_questionnaires', 'campus_id')) $table->dropForeign(['campus_id']);
            if (Schema::hasColumn('student_questionnaires', 'academic_period_id')) $table->dropForeign(['academic_period_id']);
            $table->dropColumn(array_filter(['campus_id', 'academic_period_id'], fn($c) => Schema::hasColumn('student_questionnaires', $c)));
            if (Schema::hasColumn('student_questionnaires', 'deleted_at')) $table->dropSoftDeletes();
        });

        Schema::table('prospects', function (Blueprint $table) {
            if (Schema::hasColumn('prospects', 'campus_id')) $table->dropForeign(['campus_id']);
            if (Schema::hasColumn('prospects', 'academic_period_id')) $table->dropForeign(['academic_period_id']);
            $table->dropColumn(array_filter(['campus_id', 'academic_period_id'], fn($c) => Schema::hasColumn('prospects', $c)));
            if (Schema::hasColumn('prospects', 'deleted_at')) $table->dropSoftDeletes();
        });

        Schema::table('follow_ups', function (Blueprint $table) {
            if (Schema::hasColumn('follow_ups', 'campus_id')) $table->dropForeign(['campus_id']);
            if (Schema::hasColumn('follow_ups', 'academic_period_id')) $table->dropForeign(['academic_period_id']);
            $table->dropColumn(array_filter(['campus_id', 'academic_period_id'], fn($c) => Schema::hasColumn('follow_ups', $c)));
        });

        Schema::table('marketing_routes', function (Blueprint $table) {
            if (Schema::hasColumn('marketing_routes', 'campus_id')) $table->dropForeign(['campus_id']);
            if (Schema::hasColumn('marketing_routes', 'academic_period_id')) $table->dropForeign(['academic_period_id']);
            $table->dropColumn(array_filter(['campus_id', 'academic_period_id'], fn($c) => Schema::hasColumn('marketing_routes', $c)));
        });

        Schema::table('visit_plans', function (Blueprint $table) {
            if (Schema::hasColumn('visit_plans', 'campus_id')) $table->dropForeign(['campus_id']);
            if (Schema::hasColumn('visit_plans', 'academic_period_id')) $table->dropForeign(['academic_period_id']);
            $table->dropColumn(array_filter(['campus_id', 'academic_period_id'], fn($c) => Schema::hasColumn('visit_plans', $c)));
        });

        Schema::table('school_visits', function (Blueprint $table) {
            if (Schema::hasColumn('school_visits', 'campus_id')) $table->dropForeign(['campus_id']);
            if (Schema::hasColumn('school_visits', 'academic_period_id')) $table->dropForeign(['academic_period_id']);
            $table->dropColumn(array_filter(['campus_id', 'academic_period_id'], fn($c) => Schema::hasColumn('school_visits', $c)));
        });

        Schema::table('schools', function (Blueprint $table) {
            if (Schema::hasColumn('schools', 'campus_id')) {
                $table->dropForeign(['campus_id']);
                $table->dropColumn('campus_id');
            }
        });

        Schema::dropIfExists('academic_periods');
        Schema::dropIfExists('campuses');
    }
};
