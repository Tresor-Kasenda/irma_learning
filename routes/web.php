<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Http\Controllers\Frontends\DetailFormationController;
use App\Http\Controllers\Frontends\FormationAccessController;
use App\Http\Controllers\Frontends\FormationsController;
use App\Http\Controllers\Frontends\HomePageController;
use App\Http\Controllers\Student\DashboardPageController;
use App\Http\Controllers\Student\EnrollmentController;
use App\Http\Controllers\Student\ExamController;
use App\Http\Controllers\Student\Formations\StudentCertificationController;
use App\Http\Controllers\Student\Formations\StudentFormationController;
use App\Http\Controllers\Student\Formations\StudentLearningController;
use App\Http\Controllers\Student\Learnings\StudentLearningPlayController;
use App\Http\Controllers\Student\PaymentController;
use App\Http\Controllers\Student\ProfileController;
use App\Models\Formation;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePageController::class)->name('home-page');
Route::get('/formations', FormationsController::class)->name('certifications');
Route::get('/{formation:slug}/show', DetailFormationController::class)->name('formation.show');
Route::get('/nos-tarifs', [HomePageController::class, 'pricings'])->name('pages.pricings');

Route::get('/account/inactive', fn () => inertia('Auth/AccountStatus', ['status' => 'inactive']))->name('account.inactive');
Route::get('/account/suspended', fn () => inertia('Auth/AccountStatus', ['status' => 'suspended']))->name('account.suspended');

Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/password', [ProfileController::class, 'edit'])->name('password.change');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'check.status', 'force.password.change'])->group(function () {
    Route::get('/dashboard', DashboardPageController::class)->name('dashboard');
    Route::get('/certificats', StudentCertificationController::class)->name('certificats');
    Route::get('/certificats/{certificate}', [StudentCertificationController::class, 'show'])->name('certificats.show');
    Route::get('/inprogress', StudentLearningController::class)->name('student.progress');

    Route::get('/learnings', StudentFormationController::class)->name('student.learnings');
    Route::get('/learnings/{formation:slug}/detaile', [StudentLearningPlayController::class, 'detailCourse'])->name('student.learnings.detail');

    Route::post('/formation/{formation:id}/enroll', function (Formation $formation) {
        $user = auth()->user();

        if (! $user->hasStudent()) {
            return redirect()->back()->with('error', 'Seuls les étudiants peuvent s\'inscrire aux formations.');
        }

        if ($user->enrollments()->where('formation_id', $formation->id)->exists()) {
            return redirect()->route('course.player', $formation->id);
        }

        if ($formation->price > 0) {
            return redirect()->route('student.payment.create', $formation);
        }

        $user->enrollments()->create([
            'formation_id' => $formation->id,
            'enrollment_date' => now(),
            'status' => EnrollmentStatusEnum::ACTIVE->value,
            'payment_status' => EnrollmentPaymentEnum::FREE,
            'amount_paid' => 0,
            'progress_percentage' => 0,
        ]);

        return redirect()->route('course.player', $formation->id);
    })->name('formation.enroll');

    Route::get('/formation/{formation:id}/validate', [FormationAccessController::class, 'create'])
        ->name('student.formations.validate-code');
    Route::post('/formation/{formation:id}/validate', [FormationAccessController::class, 'store'])
        ->middleware('throttle:5,30');

    Route::get('/formation/{formation:id}/payment', PaymentController::class)
        ->name('student.payment.create');
    Route::post('/formation/{formation:id}/payment', [PaymentController::class, 'store']);

    Route::get('/media/{chapter}/{type}', [App\Http\Controllers\MediaController::class, 'stream'])
        ->name('media.stream')
        ->where('type', 'video|pdf');

    Route::middleware('paid.access')->group(function () {
        Route::get('/learnings/{formation:id}/learn', StudentLearningPlayController::class)->name('course.player');

        Route::post('/course/{formation:id}/chapter/{chapter}/complete', [StudentLearningPlayController::class, 'completeChapter'])
            ->name('course.chapter.complete');

        Route::get('/exam/{exam}/take', [ExamController::class, 'take'])
            ->name('exam.take');
        Route::post('/exam/{exam}/save-answer', [ExamController::class, 'saveAnswer'])
            ->name('exam.save-answer');
        Route::post('/exam/{exam}/submit', [ExamController::class, 'submit'])
            ->name('exam.submit');
        Route::get('/exam/attempt/{attempt}/results', [ExamController::class, 'results'])
            ->name('exam.results');
    });

    Route::post('/certificates/{certificate}/download', [StudentCertificationController::class, 'download'])
        ->name('certificates.download');

    Route::group(['prefix' => 'enrollments'], function () {
        Route::get('/{enrollment}/invoice', EnrollmentController::class)
            ->name('enrollments.invoice');
        Route::post('/{enrollment}/refund', [EnrollmentController::class, 'refund'])
            ->name('enrollments.refund');
    });
});

Route::get('/verify-certificate/{hash}', [App\Http\Controllers\CertificateVerificationController::class, 'verify'])
    ->name('certificates.verify');

Route::get('/sitemap.xml', App\Http\Controllers\SitemapController::class)->name('sitemap');

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
