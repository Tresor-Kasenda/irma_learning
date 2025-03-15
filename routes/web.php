<?php

declare(strict_types=1);

use App\Livewire\Pages\Courses\Certifications;
use App\Livewire\Pages\Courses\FormationsLists;
use App\Livewire\Pages\Courses\HomePage;
use App\Livewire\Pages\Courses\LearningCourse;
use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\Examinatio\SubmitExamination;
use App\Livewire\Pages\StudentCourseLearning;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', HomePage::class)->name('home-page');
Route::get('/certifications', Certifications::class)->name('certifications');
Route::get('/formations-continue', FormationsLists::class)->name('formations-lists');
Route::get('/master-class/{masterClass}/formations', LearningCourse::class)->name('master-class');
Volt::route('/resultats', 'resultats')->name('resultats');

Route::get('/formation/{training}/details', App\Livewire\Pages\Formations\DetailFormation::class)->name('formation-details');

Route::middleware(['auth', 'verified', 'force.password.change'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('/courses/{masterClass}/start', StudentCourseLearning::class)->name('learning-course-student');
    Route::get('/courses/{masterClass}/learning/{chapter?}', StudentCourseLearning::class)
        ->name('student.course.learning');

    Route::get('/courses/{masterClass}/final-exam', SubmitExamination::class)
        ->name('student.course.final-exam')
        ->middleware('completed.chapters');

    Route::view('profile', 'profile')->name('profile');
});

require __DIR__ . '/auth.php';
