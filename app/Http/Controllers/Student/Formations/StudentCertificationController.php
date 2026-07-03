<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student\Formations;

use App\Enums\CertificateStatusEnum;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $certificate->download_url = $certificate->download_url;
        $certificate->verification_url = $certificate->verification_url;

        return Inertia::render('Student/Formations/Certifications/Show', [
            'certificate' => $certificate,
        ]);
    }

    public function download(Certificate $certificate, Request $request): StreamedResponse
    {
        abort_unless($certificate->user_id === auth()->id() || $request->user()->isAdmin(), 403);

        $certificate->load(['user', 'formation']);

        $pdf = Pdf::loadView('certificates.certificate', [
            'certificate' => $certificate,
        ])
            ->setPaper('a4', 'landscape')
            ->setOptions([
                'dpi' => 150,
                'defaultFont' => 'sans-serif',
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
            ]);

        $filename = sprintf('certificat-%s.pdf', $certificate->certificate_number);

        return $pdf->download($filename);
    }
}
