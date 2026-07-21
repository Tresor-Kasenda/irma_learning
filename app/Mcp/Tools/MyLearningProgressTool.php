<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Enums\EnrollmentPaymentEnum;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Retourne uniquement les formations et la progression de l’utilisateur authentifié.')]
#[IsIdempotent]
#[IsReadOnly]
final class MyLearningProgressTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response
    {
        $user = $request->user();

        if (! $user instanceof User) {
            return Response::error('Utilisateur non authentifié.');
        }

        $enrollments = Enrollment::query()
            ->where('user_id', $user->id)
            ->whereIn('payment_status', [
                EnrollmentPaymentEnum::PAID->value,
                EnrollmentPaymentEnum::FREE->value,
            ])
            ->whereHas('formation', fn ($query) => $query->active())
            ->with('formation')
            ->orderByDesc('last_accessed_at')
            ->latest('updated_at')
            ->limit(25)
            ->get();

        return Response::structured([
            'count' => $enrollments->count(),
            'formations' => $enrollments->map(fn (Enrollment $enrollment): array => [
                'formation_id' => $enrollment->formation_id,
                'title' => $enrollment->formation->title,
                'slug' => $enrollment->formation->slug,
                'status' => $enrollment->status->value,
                'progress_percentage' => (float) $enrollment->progress_percentage,
                'last_accessed_at' => $enrollment->last_accessed_at?->toIso8601String(),
                'completion_date' => $enrollment->completion_date?->toIso8601String(),
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
