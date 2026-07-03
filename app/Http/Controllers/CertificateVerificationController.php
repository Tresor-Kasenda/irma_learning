<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Certificate;
use Inertia\Inertia;
use Inertia\Response;

final class CertificateVerificationController extends Controller
{
    public function verify(string $hash): Response
    {
        $certificate = Certificate::query()
            ->with(['user', 'formation:id,title,slug'])
            ->where('verification_hash', $hash)
            ->first();

        if (! $certificate) {
            return Inertia::render('Auth/AccountStatus', [
                'status' => 'invalid-certificate',
                'message' => 'Ce certificat est introuvable. Vérifiez le lien de vérification.',
            ]);
        }

        if (! $certificate->isValid()) {
            return Inertia::render('Auth/AccountStatus', [
                'status' => 'expired-certificate',
                'message' => 'Ce certificat a expiré ou a été révoqué.',
            ]);
        }

        return Inertia::render('Student/Formations/Certifications/Verify', [
            'certificate' => [
                'certificate_number' => $certificate->certificate_number,
                'holder_name' => $certificate->user->name,
                'formation_title' => $certificate->formation->title,
                'issue_date' => $certificate->issue_date?->format('d/m/Y'),
                'final_score' => $certificate->final_score,
                'valid' => true,
            ],
        ]);
    }
}
