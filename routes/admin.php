<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AccessCodeController;
use App\Http\Controllers\Admin\CertificateStudentController;
use App\Http\Controllers\Admin\ChapterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EnrollmentController;
use App\Http\Controllers\Admin\ExamAttemptController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\FormationController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\SystemSettingController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserProgressController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Administration (Inertia + Vue)
|--------------------------------------------------------------------------
| Filament a été retiré (Lot 7). Le panel Inertia occupe désormais « /admin ».
| Voir docs/ pour le plan de migration.
*/

Route::middleware(['auth', 'admin.access'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', DashboardController::class)->name('dashboard');

        Route::get('formations', [FormationController::class, 'index'])->name('formations.index');
        Route::get('formations/create', [FormationController::class, 'create'])->name('formations.create');
        Route::post('formations', [FormationController::class, 'store'])->name('formations.store');
        Route::get('formations/{formation:id}', [FormationController::class, 'show'])->name('formations.show');
        Route::get('formations/{formation:id}/edit', [FormationController::class, 'edit'])->name('formations.edit');
        Route::post('formations/{formation:id}', [FormationController::class, 'update'])->name('formations.update');
        Route::delete('formations/{formation:id}', [FormationController::class, 'destroy'])->name('formations.destroy');
        Route::patch('formations/{formation:id}/toggle-active', [FormationController::class, 'toggleActive'])->name('formations.toggle-active');

        Route::get('sections', [SectionController::class, 'index'])->name('sections.index');
        Route::get('sections/create', [SectionController::class, 'create'])->name('sections.create');
        Route::post('sections', [SectionController::class, 'store'])->name('sections.store');
        Route::get('sections/{section}', [SectionController::class, 'show'])->name('sections.show');
        Route::get('sections/{section}/edit', [SectionController::class, 'edit'])->name('sections.edit');
        Route::post('sections/{section}', [SectionController::class, 'update'])->name('sections.update');
        Route::delete('sections/{section}', [SectionController::class, 'destroy'])->name('sections.destroy');
        Route::patch('sections/{section}/toggle-active', [SectionController::class, 'toggleActive'])->name('sections.toggle-active');

        Route::get('chapters', [ChapterController::class, 'index'])->name('chapters.index');
        Route::get('chapters/create', [ChapterController::class, 'create'])->name('chapters.create');
        Route::post('chapters', [ChapterController::class, 'store'])->name('chapters.store');
        Route::get('chapters/{chapter}', [ChapterController::class, 'show'])->name('chapters.show');
        Route::get('chapters/{chapter}/edit', [ChapterController::class, 'edit'])->name('chapters.edit');
        Route::post('chapters/{chapter}', [ChapterController::class, 'update'])->name('chapters.update');
        Route::post('chapters/{chapter}/extract-pdf', [ChapterController::class, 'extractPdf'])->name('chapters.extract-pdf');
        Route::delete('chapters/{chapter}', [ChapterController::class, 'destroy'])->name('chapters.destroy');
        Route::patch('chapters/{chapter}/toggle-active', [ChapterController::class, 'toggleActive'])->name('chapters.toggle-active');

        Route::get('exams', [ExamController::class, 'index'])->name('exams.index');
        Route::get('exams/create', [ExamController::class, 'create'])->name('exams.create');
        Route::post('exams', [ExamController::class, 'store'])->name('exams.store');
        Route::get('exams/{exam:id}', [ExamController::class, 'show'])->name('exams.show');
        Route::get('exams/{exam:id}/edit', [ExamController::class, 'edit'])->name('exams.edit');
        Route::post('exams/{exam:id}', [ExamController::class, 'update'])->name('exams.update');
        Route::delete('exams/{exam:id}', [ExamController::class, 'destroy'])->name('exams.destroy');
        Route::patch('exams/{exam:id}/toggle-active', [ExamController::class, 'toggleActive'])->name('exams.toggle-active');
        Route::post('exams/{exam:id}/duplicate', [ExamController::class, 'duplicate'])->name('exams.duplicate');
        Route::post('exams/bulk/activate', [ExamController::class, 'bulkActivate'])->name('exams.bulk.activate');
        Route::post('exams/bulk/deactivate', [ExamController::class, 'bulkDeactivate'])->name('exams.bulk.deactivate');
        Route::post('exams/bulk/duplicate', [ExamController::class, 'bulkDuplicate'])->name('exams.bulk.duplicate');

        Route::post('exams/{exam:id}/questions', [QuestionController::class, 'store'])->name('exams.questions.store');
        Route::post('exams/{exam:id}/questions/reorder', [QuestionController::class, 'reorder'])->name('exams.questions.reorder');
        Route::post('exams/{exam:id}/questions/{question}', [QuestionController::class, 'update'])->name('exams.questions.update');
        Route::delete('exams/{exam:id}/questions/{question}', [QuestionController::class, 'destroy'])->name('exams.questions.destroy');

        Route::get('attempts', [ExamAttemptController::class, 'index'])->name('attempts.index');
        Route::get('attempts/{attempt}', [ExamAttemptController::class, 'show'])->name('attempts.show');
        Route::post('attempts/{attempt}/complete', [ExamAttemptController::class, 'complete'])->name('attempts.complete');

        Route::get('enrollments', [EnrollmentController::class, 'index'])->name('enrollments.index');
        Route::get('certificates', [CertificateStudentController::class, 'index'])->name('certificates.index');
        Route::get('certificates/{user}', [CertificateStudentController::class, 'show'])->name('certificates.show');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::post('users', [UserController::class, 'store'])->name('users.store');
        Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
        Route::get('settings', [SystemSettingController::class, 'edit'])->name('settings.edit');
        Route::post('settings', [SystemSettingController::class, 'update'])->name('settings.update');

        Route::get('access-codes', [AccessCodeController::class, 'index'])->name('access-codes.index');
        Route::post('access-codes/generate', [AccessCodeController::class, 'generate'])->name('access-codes.generate');
        Route::get('access-codes/export', [AccessCodeController::class, 'export'])->name('access-codes.export');
        Route::delete('access-codes/{code}', [AccessCodeController::class, 'destroy'])->name('access-codes.destroy');

        Route::get('progress', [UserProgressController::class, 'index'])->name('progress.index');
        Route::post('progress/{progress}/mark-started', [UserProgressController::class, 'markStarted'])->name('progress.mark-started');
        Route::post('progress/{progress}/mark-completed', [UserProgressController::class, 'markCompleted'])->name('progress.mark-completed');
        Route::post('progress/bulk-mark-started', [UserProgressController::class, 'bulkMarkStarted'])->name('progress.bulk-mark-started');
        Route::post('progress/bulk-mark-completed', [UserProgressController::class, 'bulkMarkCompleted'])->name('progress.bulk-mark-completed');
    });
