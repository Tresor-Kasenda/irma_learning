<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Http\Controllers\Dashboard\DashboardPageController;
use App\Http\Controllers\Dashboard\Formations\StudentCertificationController;
use App\Http\Controllers\Dashboard\Formations\StudentFormationController;
use App\Http\Controllers\Dashboard\Formations\StudentLearningController;
use App\Http\Controllers\Dashboard\Learnings\StudentLearningPlayController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\Frontends\DetailFormationController;
use App\Http\Controllers\Frontends\FormationAccessController;
use App\Http\Controllers\Frontends\FormationsController;
use App\Http\Controllers\Frontends\HomePageController;
use App\Http\Controllers\Frontends\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Models\Formation;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePageController::class)->name('home-page');
Route::get('/formations', FormationsController::class)->name('certifications');
Route::get('/{formation:slug}/show', DetailFormationController::class)->name('formation.show');
Route::get('/nos-tarifs', [HomePageController::class, 'pricings'])->name('pages.pricings');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardPageController::class)->name('dashboard');
    Route::get('/certificats', StudentCertificationController::class)->name('certificats');
    Route::get('/certificats/{certificate}', [StudentCertificationController::class, 'show'])->name('certificats.show');
    Route::get('/inprogress', StudentLearningController::class)->name('student.progress');

    Route::get('/learnings', StudentFormationController::class)->name('student.learnings');
    Route::get('/learnings/{formation:slug}/detaile', [StudentLearningPlayController::class, 'detailCourse'])->name('student.learnings.detail');
    Route::get('/learnings/{formation:id}/learn', StudentLearningPlayController::class)->name('course.player');

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
    Route::post('/formation/{formation:id}/validate', [FormationAccessController::class, 'store']);

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/formation/{formation:id}/payment', PaymentController::class)
        ->name('student.payment.create');
    Route::post('/formation/{formation:id}/payment', [PaymentController::class, 'store']);

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

    Route::group(['prefix' => 'enrollments'], function () {
        Route::get('/{enrollment}/invoice', EnrollmentController::class)
            ->name('enrollments.invoice');
        Route::post('/{enrollment}/refund', [EnrollmentController::class, 'refund'])
            ->name('enrollments.refund');
    });
});

require __DIR__.'/auth.php';
