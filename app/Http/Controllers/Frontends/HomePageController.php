<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontends;

use App\Http\Controllers\Controller;
use App\Models\Formation;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

final class HomePageController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Frontends/HomePage', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
            'formation' => Formation::query()->active()->latest('created_at')->first(),
        ]);
    }

    public function pricings()
    {
        return Inertia::render('Frontends/Pricings');
    }
}
