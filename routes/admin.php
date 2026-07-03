<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\ChapterController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FormationController;
use App\Http\Controllers\Admin\SectionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Administration (Inertia + Vue)
|--------------------------------------------------------------------------
| Préfixe TEMPORAIRE « /manage » pendant la coexistence avec Filament (qui
| occupe « /admin »). Au cutover (retrait de Filament), changer le préfixe
| ci-dessous en « admin » — les noms de routes restent « admin.* ».
| Voir docs/ pour le plan de migration.
*/

Route::middleware(['auth', 'admin.access'])
    ->prefix('manage')
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
    });
