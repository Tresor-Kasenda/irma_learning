<?php

namespace App\Http\Controllers\Dashboard\Formations;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class StudentFormationController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Dashboard/Formations/Index', []);
    }
}
