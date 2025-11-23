<?php

declare(strict_types=1);

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Livewire\Pages\Certifications;
use App\Livewire\Pages\Courses\CoursePlayer;
use App\Livewire\Pages\Courses\LearningCourse;
use App\Livewire\Pages\DetailFormation;
use App\Livewire\Pages\Exams\ExamResults;
use App\Livewire\Pages\Exams\TakeExam;
use App\Livewire\Pages\Frontend\Payments\StudentPayment;
use App\Livewire\Pages\HomePage;
use App\Livewire\Pages\Profile;
use App\Livewire\Pages\Students\DashboardStudent;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/**
 * Formations d'informations
 */
Route::get('/', HomePage::class)->name('home-page');
Route::get('/{formation}/show', DetailFormation::class)->name('formation.show');
Route::get('/certifications', Certifications::class)->name('certifications');
Volt::route('/nos-tarifs', 'pages.pricings')->name('pages.pricings');

Route::middleware('auth')->group(function () {
    // Redirection vers la liste des formations après connexion
    Route::get('/dashboard', DashboardStudent::class)->name('dashboard');

    // Page profil utilisateur avec statistiques
    Route::get('/profile', Profile::class)->name('profile');

    Route::get('/dash', [DashboardController::class, 'index'])->name('content');
    Route::get('/formation/{formation}/payment', StudentPayment::class)
        ->name('student.payment.create');

    // Nouvelle route pour le lecteur de cours type Udemy
    Route::get('/course/{formation}/learn', CoursePlayer::class)
        ->name('course.player');

    // Routes pour les examens
    Route::get('/exam/{exam}/take', TakeExam::class)
        ->name('exam.take');
    Route::get('/exam/attempt/{attempt}/results', ExamResults::class)
        ->name('exam.results');

    // Ancienne route master-class (à conserver pour compatibilité)
    Route::get('/master-class/{masterClass}/formations', LearningCourse::class)
        ->name('master-class')
        ->middleware('restrict.student.access');

    Route::group(['prefix' => 'enrollments'], function () {
        Route::get('/{enrollment}/invoice', EnrollmentController::class)
            ->name('enrollments.invoice');
        Route::post('/{enrollment}/refund', [EnrollmentController::class, 'refund'])
            ->name('enrollments.refund');
    });
});

require __DIR__ . '/auth.php';
