<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dashboard\Formations;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Inertia\Inertia;
use Inertia\Response;

final class StudentCertificationController extends Controller
{
    public function __invoke()
    {
        return Inertia::render('Dashboard/Formations/Certifications/Index', []);
    }

    public function show(Certificate $certificate): Response
    {
        abort_unless($certificate->user_id === auth()->id(), 403);

        $certificate->load(['formation', 'user']);

        return Inertia::render('Dashboard/Formations/Certifications/Show', [
            'certificate' => $certificate,
        ]);
    }
}
