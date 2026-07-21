<?php

declare(strict_types=1);

namespace App\Mcp\Tools;

use App\Models\Formation;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Retourne le détail complet d\'une formation : sections, chapitres, examen, prix et prérequis.')]
#[IsIdempotent]
#[IsReadOnly]
final class FormationDetailTool extends Tool
{
    public function handle(Request $request): Response|ResponseFactory
    {
        $arguments = $request->validate([
            'formation_id' => ['required', 'integer', 'min:1'],
        ]);

        $formation = Formation::query()
            ->with([
                'exam',
                'sections' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('order_position')
                    ->with([
                        'exam',
                        'chapters' => fn ($query) => $query
                            ->where('is_active', true)
                            ->orderBy('order_position'),
                    ]),
            ])
            ->find((int) $arguments['formation_id']);

        if (! $formation) {
            return Response::structured([
                'error' => 'Formation introuvable.',
            ])->withIsError();
        }

        $sectionCount = $formation->sections->count();
        $chapterCount = $formation->sections->sum(fn ($s) => $s->chapters->count());
        $totalDuration = $formation->sections->sum(fn ($s) => $s->chapters->sum('duration_minutes'));

        return Response::structured([
            'id' => $formation->id,
            'title' => $formation->title,
            'slug' => $formation->slug,
            'short_description' => $formation->short_description,
            'description' => $formation->description,
            'level' => $formation->difficulty_level?->value,
            'price' => (float) $formation->price,
            'is_certifying' => $formation->is_certifying,
            'is_featured' => $formation->is_featured,
            'tags' => $formation->tags ?? [],
            'image_url' => $formation->image ? '/storage/'.$formation->image : null,
            'stats' => [
                'sections_count' => $sectionCount,
                'chapters_count' => $chapterCount,
                'total_duration_minutes' => $totalDuration,
                'estimated_hours' => $formation->duration_hours,
            ],
            'final_exam' => $formation->exam
                ? [
                    'id' => $formation->exam->id,
                    'title' => $formation->exam->title,
                    'description' => $formation->exam->description,
                    'duration_minutes' => $formation->exam->duration_minutes,
                    'passing_score' => $formation->exam->passing_score,
                    'max_attempts' => $formation->exam->max_attempts,
                    'questions_count' => $formation->exam->questions()->count(),
                ]
                : null,
            'sections' => $formation->sections->map(fn ($section): array => [
                'id' => $section->id,
                'title' => $section->title,
                'order_position' => $section->order_position,
                'duration_minutes' => $section->chapters->sum('duration_minutes'),
                'chapters_count' => $section->chapters->count(),
                'section_exam' => $section->exam
                    ? [
                        'id' => $section->exam->id,
                        'title' => $section->exam->title,
                        'duration_minutes' => $section->exam->duration_minutes,
                        'passing_score' => $section->exam->passing_score,
                        'questions_count' => $section->exam->questions()->count(),
                    ]
                    : null,
                'chapters' => $section->chapters->map(fn ($chapter): array => [
                    'id' => $chapter->id,
                    'title' => $chapter->title,
                    'description' => $chapter->description,
                    'content_type' => $chapter->content_type,
                    'duration_minutes' => $chapter->duration_minutes,
                    'order_position' => $chapter->order_position,
                    'is_free' => $chapter->is_free,
                ])->all(),
            ])->all(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'formation_id' => $schema->integer()
                ->description('Identifiant numérique de la formation.'),
        ];
    }
}
