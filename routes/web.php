<?php

use App\Http\Controllers\ArticleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FollowUpController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\MarketingActivityController;
use App\Http\Controllers\MarketingRouteController;
use App\Http\Controllers\MasterDataController;
use App\Http\Controllers\PriorityController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\SchoolVisitController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\StudyProgramController;
use App\Http\Controllers\StudentQuestionnaireController;
use App\Http\Controllers\StudentFollowUpController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitPlanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root to dashboard if logged in, else login
Route::get('/', function () {
    return redirect()->route('login');
});

// Public Access Field Visit Input Portal (No Login Required)
Route::get('/input-kunjungan', [\App\Http\Controllers\PublicVisitController::class, 'index'])->name('public-visit.index');
Route::post('/input-kunjungan', [\App\Http\Controllers\PublicVisitController::class, 'store'])->name('public-visit.store')->middleware('throttle:30,1');
Route::get('/kuisioner-siswa', [StudentQuestionnaireController::class, 'create'])->name('student-questionnaire.create');
Route::post('/kuisioner-siswa', [StudentQuestionnaireController::class, 'store'])->name('student-questionnaire.store')->middleware('throttle:30,1');

// Support Event Sekolah - Pengajuan Publik dari Sekolah (Tanpa Login)
Route::get('/pengajuan-event', [\App\Http\Controllers\SchoolEventProposalController::class, 'publicCreate'])->name('school-events.create');
Route::post('/pengajuan-event', [\App\Http\Controllers\SchoolEventProposalController::class, 'publicStore'])->name('school-events.store')->middleware('throttle:30,1');
Route::get('/pengajuan-event/sukses/{reference_no}', [\App\Http\Controllers\SchoolEventProposalController::class, 'publicSuccess'])->name('school-events.success');

// Hasil Support Event - Pendaftaran Siswa Event (Tanpa Login & Tanpa Kuisioner)
Route::get('/hasil-support-event', [\App\Http\Controllers\EventStudentLeadController::class, 'publicCreate'])->name('event-students.create');
Route::post('/hasil-support-event', [\App\Http\Controllers\EventStudentLeadController::class, 'publicStore'])->name('event-students.store')->middleware('throttle:30,1');
Route::get('/hasil-support-event/sukses', [\App\Http\Controllers\EventStudentLeadController::class, 'publicSuccess'])->name('event-students.success');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

Route::post('/logout', [LogoutController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Application Routes
Route::middleware(['auth'])->group(function () {
    // 1. Dashboard: Accessible by all authenticated users
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. CRM, Prospek, Kuisioner & Follow Up Siswa (Core workspace for Customer Service, Marketing, Koordinator, Pimpinan, Admin)
    Route::get('/hasil-kuisioner/export', [StudentQuestionnaireController::class, 'downloadExcel'])->name('student-questionnaires.export');
    Route::get('/hasil-kuisioner', [StudentQuestionnaireController::class, 'index'])->name('student-questionnaires.index');

    // Follow Up Siswa Kuisioner (Minat Kuliah & Beasiswa)
    Route::get('/student-follow-ups/export', [StudentFollowUpController::class, 'export'])->name('student-follow-ups.export');
    Route::get('/student-follow-ups/import-template', [StudentFollowUpController::class, 'downloadImportTemplate'])->name('student-follow-ups.import-template');
    Route::post('/student-follow-ups/import', [StudentFollowUpController::class, 'import'])->name('student-follow-ups.import');
    Route::get('/student-follow-ups', [StudentFollowUpController::class, 'index'])->name('student-follow-ups.index');
    Route::post('/student-follow-ups/{studentQuestionnaire}', [StudentFollowUpController::class, 'store'])->name('student-follow-ups.store');
    Route::get('/student-follow-ups/{studentQuestionnaire}/history', [StudentFollowUpController::class, 'history'])->name('student-follow-ups.history');
    
    // Ploting & Pembagian Jumlah Data Follow Up (Pimpinan, Koordinator Marketing, & Admin)
    Route::post('/student-follow-ups/distribute', [StudentFollowUpController::class, 'distribute'])->name('student-follow-ups.distribute');
    Route::post('/student-follow-ups/bulk-assign', [StudentFollowUpController::class, 'bulkAssign'])->name('student-follow-ups.bulk-assign');
    Route::post('/student-follow-ups/{studentQuestionnaire}/assign', [StudentFollowUpController::class, 'assignSingle'])->name('student-follow-ups.assign-single');

    // Data Prospek / Lead CRM Routes
    Route::get('/prospects', [ProspectController::class, 'index'])->name('prospects.index');
    Route::post('/prospects', [ProspectController::class, 'store'])->name('prospects.store');
    Route::get('/prospects/import-template', [ProspectController::class, 'downloadImportTemplate'])->name('prospects.import-template');
    Route::post('/prospects/import', [ProspectController::class, 'import'])->name('prospects.import');
    Route::post('/prospects/{prospect}/validate', [ProspectController::class, 'validateStudent'])->name('prospects.validate');
    Route::put('/prospects/{prospect}/status', [ProspectController::class, 'updateStatus'])->name('prospects.update-status');
    Route::delete('/prospects/{prospect}', [ProspectController::class, 'destroy'])->name('prospects.destroy');

    // Menu Mahasiswa Baru (Official Validated Students Directory)
    Route::get('/new-students', [\App\Http\Controllers\NewStudentController::class, 'index'])->name('new-students.index');
    Route::get('/new-students/export', [\App\Http\Controllers\NewStudentController::class, 'export'])->name('new-students.export');
    Route::post('/new-students/{prospect}/unvalidate', [\App\Http\Controllers\NewStudentController::class, 'unvalidate'])->name('new-students.unvalidate');

    // Follow Up Routes (Sekolah)
    Route::get('/follow-ups/export', [FollowUpController::class, 'export'])->name('follow-ups.export');
    Route::get('/follow-ups', [FollowUpController::class, 'index'])->name('follow-ups.index');
    Route::post('/follow-ups/sync', [FollowUpController::class, 'syncFromVisits'])->name('follow-ups.sync');
    Route::post('/follow-ups', [FollowUpController::class, 'store'])->name('follow-ups.store');
    Route::put('/follow-ups/{followUp}/schedule', [FollowUpController::class, 'updateSchedule'])->name('follow-ups.update-schedule');
    Route::put('/follow-ups/{followUp}/status', [FollowUpController::class, 'updateStatus'])->name('follow-ups.update-status');
    Route::delete('/follow-ups/{followUp}', [FollowUpController::class, 'destroy'])->name('follow-ups.destroy');

    // Data Sekolah & GIS Routes: Reference data
    Route::get('/schools/export', [\App\Http\Controllers\SchoolImportController::class, 'exportData'])->name('schools.export');
    Route::get('/schools/export-template', [\App\Http\Controllers\SchoolImportController::class, 'downloadTemplate'])->name('schools.export-template');
    Route::post('/schools/import', [\App\Http\Controllers\SchoolImportController::class, 'import'])->name('schools.import');
    Route::post('/schools/integrate', [\App\Http\Controllers\SchoolIntegrationController::class, 'integrate'])->name('schools.integrate');
    Route::get('/schools/provinces', [\App\Http\Controllers\SchoolIntegrationController::class, 'provinces'])->name('schools.provinces');
    Route::get('/schools/regencies', [\App\Http\Controllers\SchoolIntegrationController::class, 'regencies'])->name('schools.regencies');
    Route::get('/schools/districts', [\App\Http\Controllers\SchoolIntegrationController::class, 'districts'])->name('schools.districts');
    Route::post('/schools/{school}/coordinates', [SchoolController::class, 'updateCoordinates'])->name('schools.update-coordinates');
    Route::post('/schools/restore-all', [SchoolController::class, 'restoreAll'])->name('schools.restore-all');
    Route::post('/schools/{id}/restore', [SchoolController::class, 'restore'])->name('schools.restore');
    Route::match(['DELETE', 'POST'], '/schools/destroy-all', [SchoolController::class, 'destroyAll'])->name('schools.destroy-all');
    Route::resource('schools', SchoolController::class);

    // Smart School Mapping GIS Routes
    Route::get('/map', [MapController::class, 'index'])->name('map.index');
    Route::get('/api/map/schools', [MapController::class, 'apiSchools'])->name('api.map.schools');

    // Master Program Studi Routes (Index is open for viewing; mutations restricted to Admin & Pimpinan)
    Route::get('/study-programs', [StudyProgramController::class, 'index'])->name('study-programs.index');
    Route::middleware('role:Admin,Pimpinan')->group(function () {
        Route::post('/study-programs', [StudyProgramController::class, 'store'])->name('study-programs.store');
        Route::put('/study-programs/{studyProgram}', [StudyProgramController::class, 'update'])->name('study-programs.update');
        Route::delete('/study-programs/{studyProgram}', [StudyProgramController::class, 'destroy'])->name('study-programs.destroy');
    });

    // Master QR Code Center Routes (CS & Marketing can view & download QR codes for forms)
    Route::get('/qr-codes', [\App\Http\Controllers\QrCodeController::class, 'index'])->name('qr-codes.index');
    Route::get('/qr-codes/download/{type}', [\App\Http\Controllers\QrCodeController::class, 'download'])->name('qr-codes.download');

    // Laporan & Analytics Routes
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

    // Informasi PMB UBSI
    Route::get('/informasi-pmb', [\App\Http\Controllers\PmbController::class, 'index'])->name('pmb.index');

    // E-Brosur PMB Routes (Upload, View, Download)
    Route::get('/e-brosur', [\App\Http\Controllers\EBrochureController::class, 'index'])->name('e-brochures.index');
    Route::post('/e-brosur', [\App\Http\Controllers\EBrochureController::class, 'store'])->name('e-brochures.store');
    Route::get('/e-brosur/{eBrochure}/view', [\App\Http\Controllers\EBrochureController::class, 'show'])->name('e-brochures.show');
    Route::get('/e-brosur/{eBrochure}/download', [\App\Http\Controllers\EBrochureController::class, 'download'])->name('e-brochures.download');
    Route::delete('/e-brosur/{eBrochure}', [\App\Http\Controllers\EBrochureController::class, 'destroy'])->name('e-brochures.destroy');

    // Support Event Sekolah (Proposal Masuk & Laporan Kegiatan Terintegrasi)
    Route::get('/event-proposals', [\App\Http\Controllers\SchoolEventProposalController::class, 'index'])->name('event-proposals.index');
    Route::put('/event-proposals/{proposal}/status', [\App\Http\Controllers\SchoolEventProposalController::class, 'updateStatus'])->name('event-proposals.update-status');
    Route::get('/event-proposals/{proposal}/download', [\App\Http\Controllers\SchoolEventProposalController::class, 'downloadProposal'])->name('event-proposals.download');
    Route::delete('/event-proposals/{proposal}', [\App\Http\Controllers\SchoolEventProposalController::class, 'destroy'])->name('event-proposals.destroy');

    Route::get('/event-reports', [\App\Http\Controllers\SchoolEventProposalController::class, 'reportsIndex'])->name('event-reports.index');
    Route::post('/event-reports/{proposal}/documentation', [\App\Http\Controllers\SchoolEventProposalController::class, 'updateReport'])->name('event-reports.update-documentation');

    // Hasil Support Event - Data Siswa Event (Tersinkron ke Follow Up)
    Route::get('/hasil-support-event-data', [\App\Http\Controllers\EventStudentLeadController::class, 'index'])->name('event-students.index');
    Route::get('/hasil-support-event-data/template', [\App\Http\Controllers\EventStudentLeadController::class, 'downloadTemplate'])->name('event-students.template');
    Route::post('/hasil-support-event-data/import', [\App\Http\Controllers\EventStudentLeadController::class, 'import'])->name('event-students.import');
    Route::get('/hasil-support-event-data/export', [\App\Http\Controllers\EventStudentLeadController::class, 'export'])->name('event-students.export');

    // 3. Prioritas Sekolah (Scoring & Ranking - Admin, Pimpinan, Koordinator, Marketing)
    Route::middleware('role:Admin,Pimpinan,Koordinator Marketing,Marketing')->group(function () {
        Route::get('/priorities', [PriorityController::class, 'index'])->name('priorities.index');
        Route::get('/priorities/student-intakes-template', [PriorityController::class, 'downloadStudentIntakeTemplate'])->name('priorities.student-intakes-template');
        Route::post('/priorities/import-student-intakes', [PriorityController::class, 'importStudentIntakes'])->name('priorities.import-student-intakes');
        Route::post('/priorities/recalculate', [PriorityController::class, 'recalculate'])->name('priorities.recalculate');
    });

    // 4. Operasional Lapangan (Field Operations - Admin, Pimpinan, Koordinator, Marketing)
    Route::middleware('role:Admin,Pimpinan,Koordinator Marketing,Marketing')->group(function () {
        Route::resource('visit-plans', VisitPlanController::class)->except(['show']);
        Route::resource('visits', SchoolVisitController::class)->except(['show']);
        Route::get('/socializations-completed', [FollowUpController::class, 'completedSocializations'])->name('socializations.completed.index');
        Route::put('/follow-ups/{followUp}/complete-socialization', [FollowUpController::class, 'confirmSocialization'])->name('socializations.completed.confirm');
        Route::delete('/follow-ups/{followUp}/complete-socialization', [FollowUpController::class, 'reopenSocialization'])->name('socializations.completed.reopen');

        // Rute Marketing (Daily 10-School Clustering & 10-Day Outing Campaign)
        Route::get('/marketing-routes', [MarketingRouteController::class, 'index'])->name('marketing-routes.index');
        Route::post('/marketing-routes/auto-cluster', [MarketingRouteController::class, 'storeAutoCluster'])->name('marketing-routes.store-cluster');
        Route::post('/marketing-routes/10-day-campaign', [MarketingRouteController::class, 'store10DayCampaign'])->name('marketing-routes.store-campaign');
        Route::get('/api/marketing-routes/cluster-preview', [MarketingRouteController::class, 'apiGenerateCluster'])->name('api.marketing-routes.cluster-preview');
        Route::delete('/marketing-routes/{marketingRoute}', [MarketingRouteController::class, 'destroy'])->name('marketing-routes.destroy');

        // Aktivitas Marketing
        Route::resource('activities', MarketingActivityController::class)->except(['create', 'show', 'edit']);
    });

    // 5. Master Data Routes (Admin & Pimpinan)
    Route::middleware('role:Admin,Pimpinan')->group(function () {
        Route::get('/master-data', [MasterDataController::class, 'index'])->name('master-data.index');
        Route::post('/master-data/school-types', [MasterDataController::class, 'storeSchoolType'])->name('master-data.school-types.store');
        Route::post('/master-data/accreditations', [MasterDataController::class, 'storeAccreditation'])->name('master-data.accreditations.store');
        Route::post('/master-data/positions', [MasterDataController::class, 'storeContactPosition'])->name('master-data.positions.store');
        Route::put('/master-data/scoring/{criterion}', [MasterDataController::class, 'updateScoringCriterion'])->name('master-data.scoring.update');
    });

    // 6. User Management & Pengaturan Sistem (Admin Only)
    Route::middleware('role:Admin')->group(function () {
        Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/close-period', [SettingsController::class, 'closePeriod'])->name('settings.close-period');

        // Pengajuan Tutup Periode oleh Admin
        Route::post('/period-closing/request', [\App\Http\Controllers\PeriodClosingController::class, 'storeRequest'])->name('period-closing.request');
        Route::post('/period-closing/{periodClosingRequest}/cancel', [\App\Http\Controllers\PeriodClosingController::class, 'cancel'])->name('period-closing.cancel');

        // Notifikasi WhatsApp Otomatis (H-1 Kunjungan, H-5 Sosialisasi, H-0 Proposal, H-7 Event)
        Route::get('/whatsapp-notifications', [\App\Http\Controllers\WhatsAppNotificationController::class, 'index'])->name('whatsapp-notifications.index');
        Route::post('/whatsapp-notifications/settings', [\App\Http\Controllers\WhatsAppNotificationController::class, 'updateSettings'])->name('whatsapp-notifications.update-settings');
        Route::post('/whatsapp-notifications/send-test', [\App\Http\Controllers\WhatsAppNotificationController::class, 'sendTest'])->name('whatsapp-notifications.send-test');
        Route::post('/whatsapp-notifications/trigger-reminders', [\App\Http\Controllers\WhatsAppNotificationController::class, 'triggerReminders'])->name('whatsapp-notifications.trigger-reminders');
    });

    // 7. Multi-Stakeholder Tutup Periode Review & Keputusan (Admin, Pimpinan, Koordinator Marketing, Customer Service)
    Route::middleware('role:Admin,Pimpinan,Koordinator Marketing,Customer Service')->group(function () {
        Route::get('/period-closing', [\App\Http\Controllers\PeriodClosingController::class, 'index'])->name('period-closing.index');
        Route::post('/period-closing/{periodClosingRequest}/respond', [\App\Http\Controllers\PeriodClosingController::class, 'respond'])->name('period-closing.respond');
    });

    // 7. WordCraft AI — Sistem Otomatisasi Artikel (Dinonaktifkan)
    // Route::prefix('articles')->name('articles.')->group(function () {
    //     Route::get('/', [ArticleController::class, 'index'])->name('index');
    //     Route::get('/create', [ArticleController::class, 'create'])->name('create');
    //     Route::post('/paraphrase-titles', [ArticleController::class, 'paraphraseTitles'])->name('paraphrase-titles');
    //     Route::post('/', [ArticleController::class, 'store'])->name('store');
    //     Route::get('/settings', [ArticleController::class, 'settings'])->name('settings');
    //     Route::post('/settings', [ArticleController::class, 'updateSettings'])->name('settings.update');
    //     Route::get('/download-zip', [ArticleController::class, 'downloadZip'])->name('download-zip');
    //     Route::post('/sync-json', [ArticleController::class, 'syncJson'])->name('sync-json');
    //     Route::post('/bulk-destroy', [ArticleController::class, 'bulkDestroy'])->name('bulk-destroy');
    //     Route::get('/{article}', [ArticleController::class, 'show'])->name('show');
    //     Route::get('/{article}/download', [ArticleController::class, 'downloadDocx'])->name('download');
    //     Route::match(['GET', 'POST'], '/{article}/retry', [ArticleController::class, 'retry'])->name('retry');
    //     Route::match(['GET', 'POST'], '/{article}/process-now', [ArticleController::class, 'processNow'])->name('process-now');
    //     Route::delete('/{article}', [ArticleController::class, 'destroy'])->name('destroy');
    // });
});
