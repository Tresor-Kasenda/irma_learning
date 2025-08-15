<?php

declare(strict_types=1);

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Livewire\Pages\Courses\Certifications;
use App\Livewire\Pages\Courses\FormationsLists;
use App\Livewire\Pages\Courses\LearningCourse;
use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\Frontend\Formations;
use App\Livewire\Pages\Frontend\Payments\StudentPayment;
use App\Livewire\Pages\Frontend\ShowFormation\DetailFormation;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

/**
 * Formations d'informations
 */
Route::get('/', Formations::class)->name('home-page');
Route::get('/{formation}/show', DetailFormation::class)
    ->name('formation.show');

Route::get('/certifications', Certifications::class)->name('certifications');
Route::get('/formations-continue', FormationsLists::class)->name('formations-lists');
Volt::route('/nos-tarifs', 'pages.pricings')->name('pages.pricings');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', Dashboard::class)->name('dashboard');
    Route::get('/dash', [DashboardController::class, 'index'])->name('content');
    Route::get('/formation/{formation}/payment', StudentPayment::class)
        ->name('student.payment.create');
    Route::get('/master-class/{masterClass}/formations', LearningCourse::class)
        ->name('master-class')
        ->middleware('restrict.student.access');

    Route::get('/inactive-account', [AccountController::class, 'inactive'])
        ->middleware('check.status:inactive');
});

require __DIR__ . '/auth.php';
