<?php

declare(strict_types=1);

use App\Http\Controllers\AccountController;
use App\Http\Controllers\DashboardController;
use App\Livewire\Pages\Courses\Certifications;
use App\Livewire\Pages\Courses\FormationsLists;
use App\Livewire\Pages\Courses\LearningCourse;
use App\Livewire\Pages\Dashboard;
use App\Livewire\Pages\Frontend\Formations;
use App\Livewire\Pages\Frontend\ShowFormation\DetailFormation;
use App\Livewire\Pages\MasterClass\MasterClassDetails;
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

Route::get('/master-class/{masterClass}/formations', LearningCourse::class)
    ->name('master-class')
    ->middleware('restrict.student.access');

Route::get('/master-class/{masterClass}/details', MasterClassDetails::class)
    ->name('master-class.details');

Route::get('/formation/{training}/details', App\Livewire\Pages\Formations\DetailFormation::class)
    ->name('formation-details');

Volt::route('/nos-tarifs', 'pages.pricings')->name('pages.pricings');

Route::get('/formation/{training}/details', App\Livewire\Pages\Formations\DetailFormation::class)->name('formation-details');


Route::get('/dash', [DashboardController::class, 'index'])
    ->middleware(['auth', 'check.status']);


Route::get('/dashboard', Dashboard::class)
    ->middleware(['auth', 'check.status:active'])
    ->name('dashboard');


Route::get('/inactive-account', [AccountController::class, 'inactive'])
    ->middleware(['auth', 'check.status:inactive']);

//Route::middleware(['auth'])
//    ->group(function () {
//        Route::get('/admin', [AdminController::class, 'index'])
//            ->middleware('check.status:active');
//
//        Route::get('/account/suspended', [AccountController::class, 'suspended'])
//            ->name('account.suspended');
//
//        Route::get('/account/inactive', [AccountController::class, 'inactive'])
//            ->name('account.inactive');
//    });

require __DIR__ . '/auth.php';
