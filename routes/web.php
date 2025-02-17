<?php

declare(strict_types=1);

use App\Livewire\Pages\Courses\Certifications;
use App\Livewire\Pages\Courses\FormationLists;
use App\Livewire\Pages\Courses\FormationsLists;
use App\Livewire\Pages\Courses\HomePage;
use App\Livewire\Pages\Courses\LearningCourse;
use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\StudentCourseLearning;
use Illuminate\Support\Facades\Route;

Route::get('/', HomePage::class)->name('home-page');
Route::get('/formations', FormationLists::class)->name('formations');
Route::get('/certifications', Certifications::class)->name('certifications');
Route::get('/formations-continue', FormationsLists::class)->name('formations-lists');

Route::middleware(['auth', 'verified', 'force.password.change'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::get('/master-class/{masterClass}/formations', LearningCourse::class)->name('master-class');
    Route::get('/courses/{masterClass}/start', StudentCourseLearning::class)->name('learning-course-student');
    Route::view('profile', 'profile')->name('profile');
});

require __DIR__ . '/auth.php';
