<?php

declare(strict_types=1);

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\DashboardController;
use App\Livewire\ConvertPdf;
use App\Livewire\Pages\Courses\Certifications;
use App\Livewire\Pages\Courses\FormationsLists;
use App\Livewire\Pages\Courses\HomePage;
use App\Livewire\Pages\Courses\LearningCourse;
use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\Examinatio\SubmitExamination;
use App\Livewire\Pages\History\StudentExamUpateHistory;
use App\Livewire\Pages\History\StudentHistory;
use App\Livewire\Pages\MasterClass\MasterClassDetails;
use App\Livewire\Pages\Student\MyMasterClasses;
use App\Livewire\Pages\StudentCourseLearning;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', HomePage::class)->name('home-page');
Route::get('/certifications', Certifications::class)->name('certifications');
Route::get('/formations-continue', FormationsLists::class)->name('formations-lists');

Route::get('/master-class/{masterClass}/formations', LearningCourse::class)
    ->name('master-class')
    ->middleware('restrict.student.access');

Route::get('/master-class/{masterClass}/details', MasterClassDetails::class)
    ->name('master-class.details');

Route::get('/formation/{training}/details', App\Livewire\Pages\Formations\DetailFormation::class)
    ->name('formation-details');

Volt::route('/nos-tarifs', 'pages.pricings')->name('pages.pricings');

Route::get('/formation/{training}/details', App\Livewire\Pages\Formations\DetailFormation::class)->name('formation-details');

Route::middleware(['auth', 'verified', 'force.password.change'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');

    Route::get('/mes-formations', MyMasterClasses::class)->name('student.my-master-classes');

    Route::get('/courses/{masterClass}/start', StudentCourseLearning::class)
        ->name('learning-course-student');

    Route::get('convert/pdf', ConvertPdf::class);

    Route::middleware('ensure.master.class.access')->group(function () {
//        Route::get('/courses/{masterClass}/start', StudentCourseLearning::class)
//            ->name('learning-course-student');
        Route::get('/courses/{masterClass}/learning/{chapter?}', StudentCourseLearning::class)
            ->name('student.course.learning');

        Route::get('/courses/{masterClass}/final-exam', SubmitExamination::class)
            ->name('student.course.final-exam')
            ->middleware('completed.chapters');
    });

    Route::view('profile', 'profile')->name('profile');

    Volt::route('/resultats', 'resultats')->name('resultats');

    Route::get('/histories', StudentHistory::class)->name('student.history.lists');
    Route::get('/histories/{submission}/update', StudentExamUpateHistory::class)->name('student.history.update');
});


Route::get('/dash', [DashboardController::class, 'index'])
    ->middleware(['auth', 'check.status']);

Route::get('/inactive-account', [AccountController::class, 'inactive'])
    ->middleware(['auth', 'check.status:inactive']);

Route::middleware(['auth'])
    ->group(function () {
        Route::get('/admin', [AdminController::class, 'index'])
            ->middleware('check.status:active');

        Route::get('/account/suspended', [AccountController::class, 'suspended'])
            ->name('account.suspended');

        Route::get('/account/inactive', [AccountController::class, 'inactive'])
            ->name('account.inactive');
    });

require __DIR__ . '/auth.php';
