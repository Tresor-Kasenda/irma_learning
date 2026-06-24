<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class DashboardPageController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Dashboard/Index', []);
    }
}
