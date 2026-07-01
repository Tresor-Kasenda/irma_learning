<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FormationController;
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
    });
