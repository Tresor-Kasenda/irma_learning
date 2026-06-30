<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Formations;

use App\Enums\CertificateStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Inertia\Inertia;
use Inertia\Response;

final class StudentCertificationController extends Controller
{
    public function __invoke(): Response
    {
        $certificates = Certificate::query()
            ->with(['formation:id,title,slug,difficulty_level'])
            ->where('user_id', auth()->id())
            ->where('status', CertificateStatusEnum::ACTIVE->value)
            ->latest('issue_date')
            ->get(['id', 'formation_id', 'certificate_number', 'final_score', 'issue_date', 'status']);

        return Inertia::render('Student/Formations/Certifications/Index', [
            'certificates' => $certificates,
        ]);
    }

    public function show(Certificate $certificate): Response
    {
        abort_unless($certificate->user_id === auth()->id(), 403);

        $certificate->load(['formation', 'user']);

        return Inertia::render('Student/Formations/Certifications/Show', [
            'certificate' => $certificate,
        ]);
    }
}
