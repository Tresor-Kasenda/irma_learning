<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('dashboard', App\Livewire\Pages\Dashboard::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/master-class/{masterClass}/formations', App\Livewire\Pages\Courses\LearningCourse::class)
    ->middleware(['auth', 'verified'])
    ->name('master-class');

Route::get('/courses/{masterClass}/start', \App\Livewire\Pages\StudentCourseLearning::class)
    ->middleware(['auth', 'verified'])
    ->name('learning-course-student');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
