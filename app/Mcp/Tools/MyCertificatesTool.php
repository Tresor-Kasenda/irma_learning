<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Retourne les certificats valides de l’utilisateur authentifié.')]
#[IsIdempotent]
#[IsReadOnly]
final class MyCertificatesTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): ResponseFactory|Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return Response::error('Utilisateur non authentifié.');
        }

        $certificates = Certificate::query()
            ->where('user_id', $user->id)
            ->valid()
            ->with('formation')
            ->latest('issue_date')
            ->get();

        return Response::structured([
            'count' => $certificates->count(),
            'certificates' => $certificates->map(fn (Certificate $certificate): array => [
                'certificate_number' => $certificate->certificate_number,
                'formation_id' => $certificate->formation_id,
                'formation_title' => $certificate->formation->title,
                'final_score' => (float) $certificate->final_score,
                'issue_date' => $certificate->issue_date?->toIso8601String(),
                'expiry_date' => $certificate->expiry_date?->toIso8601String(),
                'verification_url' => $certificate->verification_url,
            ])->all(),
        ]);
    }

    /**
     * Get the tool's input schema.
     *
     * @return array<string, JsonSchema>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
