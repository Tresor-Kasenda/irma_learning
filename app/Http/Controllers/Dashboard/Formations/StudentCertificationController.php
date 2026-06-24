<?php

namespace App\Http\Controllers\Dashboard\Formations;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class StudentCertificationController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Dashboard/Formations/Certifications/Index', []);
    }
}
