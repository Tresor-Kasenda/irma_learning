<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Formation;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Recherche les formations actuellement publiées dans le catalogue IRMA Learning.')]
#[IsIdempotent]
#[IsReadOnly]
final class SearchFormationsTool extends Tool
{
    /**
     * Handle the tool request.
     */
    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->validate([
            'query' => ['nullable', 'string', 'max:100'],
        ]);

        $search = mb_trim((string) ($arguments['query'] ?? ''));

        $formations = Formation::query()
            ->active()
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query
                        ->where('title', 'ilike', "%{$search}%")
                        ->orWhere('short_description', 'ilike', "%{$search}%")
                        ->orWhere('description', 'ilike', "%{$search}%");
                });
            })
            ->orderByDesc('is_featured')
            ->orderBy('title')
            ->limit(12)
            ->get();

        return Response::structured([
            'query' => $search,
            'count' => $formations->count(),
            'formations' => $formations->map(fn (Formation $formation): array => [
                'id' => $formation->id,
                'title' => $formation->title,
                'slug' => $formation->slug,
                'short_description' => $formation->short_description,
                'level' => $formation->difficulty_level?->value,
                'duration_hours' => $formation->duration_hours,
                'price' => (float) $formation->price,
                'is_certifying' => $formation->is_certifying,
                'tags' => $formation->tags ?? [],
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
        return [
            'query' => $schema->string()
                ->description('Texte à rechercher dans le titre et les descriptions des formations.'),
        ];
    }
}
