<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\ChapterTypeEnum;
use App\Enums\FormationLevelEnum;
use App\Enums\QuestionTypeEnum;
use App\Models\Formation;
use App\Models\Question;
use App\Models\Section;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

final class ProfessionalDemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $disk = Storage::disk('public');
        $imagePath = 'demo/professional-risk-management.jpg';
        $videoPath = 'demo/welcome-to-the-course.mp4';
        $pdfPath = 'demo/risk-register-workbook.pdf';

        $disk->put($imagePath, file_get_contents(public_path('images/course-2.jpg')));
        $disk->put($videoPath, base64_decode($this->demoVideo(), true));
        $disk->put($pdfPath, Pdf::loadHTML($this->pdfWorkbook())->output());

        $formation = Formation::query()->updateOrCreate(
            ['title' => 'Pilotage des risques opérationnels — Parcours professionnel'],
            [
                'short_description' => 'Construisez un dispositif de maîtrise des risques, de la cartographie au plan de continuité.',
                'description' => 'Un parcours professionnel fondé sur des cas réels, des supports téléchargeables et une évaluation obligatoire après chaque section.',
                'image' => $imagePath,
                'price' => 0,
                'duration_hours' => 8,
                'difficulty_level' => FormationLevelEnum::INTERMEDIATE,
                'is_active' => false,
                'is_featured' => true,
                'is_certifying' => true,
                'tags' => ['risques', 'audit', 'gouvernance', 'professionnel'],
            ],
        );

        $foundations = $this->section($formation, 1, 'Fondamentaux et gouvernance', 120);
        $mapping = $this->section($formation, 2, 'Cartographie et traitement des risques', 210);
        $continuity = $this->section($formation, 3, 'Contrôle, continuité et reporting', 150);

        $foundations->chapters()->updateOrCreate(
            ['title' => 'Bienvenue et objectifs du parcours'],
            [
                'description' => 'Présentation du parcours, des livrables et des critères de certification.',
                'content_type' => ChapterTypeEnum::VIDEO,
                'content' => '',
                'video_url' => $videoPath,
                'duration_minutes' => 5,
                'order_position' => 1,
                'is_free' => true,
                'is_active' => true,
            ],
        );
        $foundations->chapters()->updateOrCreate(
            ['title' => 'Cadre de gouvernance des risques'],
            [
                'description' => 'Rôles, responsabilités et modèle des trois lignes.',
                'content_type' => ChapterTypeEnum::TEXT,
                'content' => <<<'MARKDOWN'
# Gouvernance des risques

Une gouvernance efficace relie la stratégie, les opérations et le contrôle interne.

## Les trois lignes

1. **Métiers** — identifient et traitent les risques au quotidien.
2. **Fonctions de supervision** — définissent le cadre et accompagnent les métiers.
3. **Audit interne** — fournit une assurance indépendante.

> Livrable attendu : une matrice RACI validée par la direction.

~~~mermaid
flowchart LR
    A[Conseil] --> B[Direction générale]
    B --> C[Métiers]
    B --> D[Risque et conformité]
    A --> E[Audit interne]
~~~
MARKDOWN,
                'duration_minutes' => 18,
                'order_position' => 2,
                'is_free' => false,
                'is_active' => true,
            ],
        );

        $mapping->chapters()->updateOrCreate(
            ['title' => 'Construire un registre des risques'],
            [
                'description' => 'Support pratique avec méthode, exemple et grille de cotation.',
                'content_type' => ChapterTypeEnum::PDF,
                'media_url' => $pdfPath,
                'content' => "# Registre des risques\n\nTéléchargez le support PDF, puis appliquez la méthode au cas proposé.",
                'duration_minutes' => 35,
                'order_position' => 1,
                'is_free' => false,
                'is_active' => true,
                'processing_status' => 'completed',
                'processed_at' => now(),
                'processing_metadata' => ['page_count' => 3, 'document_type' => 'native', 'extraction_strategy' => 'text-first'],
            ],
        );
        $mapping->chapters()->updateOrCreate(
            ['title' => 'Prioriser les plans de traitement'],
            [
                'description' => 'Arbitrer entre réduction, transfert, acceptation et évitement.',
                'content_type' => ChapterTypeEnum::TEXT,
                'content' => <<<'MARKDOWN'
# Plan de traitement

Chaque action doit posséder un responsable, une échéance, un budget et un indicateur de résultat.

| Stratégie | Usage recommandé | Exemple |
|---|---|---|
| Réduire | Risque maîtrisable | Double validation des paiements |
| Transférer | Impact financier élevé | Assurance cyber |
| Accepter | Coût disproportionné | Tolérance documentée |
| Éviter | Activité hors appétence | Arrêt du processus |
MARKDOWN,
                'duration_minutes' => 22,
                'order_position' => 2,
                'is_free' => false,
                'is_active' => true,
            ],
        );

        $continuity->chapters()->updateOrCreate(
            ['title' => 'Concevoir un tableau de bord de risques'],
            [
                'description' => 'Choisir des KRI lisibles et actionnables par le comité de direction.',
                'content_type' => ChapterTypeEnum::TEXT,
                'content' => "# Reporting exécutif\n\nUn bon indicateur est relié à une décision. Présentez la tendance, le seuil d’alerte, le responsable et l’action attendue.",
                'duration_minutes' => 20,
                'order_position' => 1,
                'is_free' => false,
                'is_active' => true,
            ],
        );

        $this->exam($foundations, 'Évaluation — Fondamentaux et gouvernance', [
            ['Quel acteur fournit une assurance indépendante ?', QuestionTypeEnum::SINGLE_CHOICE, ['Audit interne', 'Équipe commerciale', 'Fournisseur'], [0]],
            ['Les métiers constituent la première ligne de maîtrise.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
        ]);
        $this->exam($mapping, 'Évaluation — Cartographie et traitement', [
            ['Quels éléments rendent une action pilotable ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Responsable', 'Échéance', 'Couleur du document', 'Indicateur'], [0, 1, 3]],
            ['Quelle stratégie supprime une activité hors appétence ?', QuestionTypeEnum::SINGLE_CHOICE, ['Éviter', 'Accepter', 'Transférer'], [0]],
        ]);
        $this->exam($continuity, 'Évaluation — Contrôle et reporting', [
            ['Un KRI doit être relié à une décision.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [0]],
            ['Que doit montrer un tableau de bord exécutif ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Tendance', 'Seuil', 'Responsable', 'Toutes les données brutes'], [0, 1, 2]],
        ]);
        $this->exam($formation, 'Examen final — Pilotage des risques opérationnels', [
            ['Quel document attribue les responsabilités ?', QuestionTypeEnum::SINGLE_CHOICE, ['Matrice RACI', 'Bon de commande', 'Journal de caisse'], [0]],
            ['Quelles réponses au risque sont valides ?', QuestionTypeEnum::MULTIPLE_CHOICE, ['Réduire', 'Transférer', 'Accepter', 'Ignorer sans décision'], [0, 1, 2]],
            ['La certification peut être délivrée sans réussir les évaluations de section.', QuestionTypeEnum::TRUE_FALSE, ['Vrai', 'Faux'], [1]],
        ], 75, 2);

        $formation->update(['is_active' => true]);
    }

    private function section(Formation $formation, int $position, string $title, int $duration): Section
    {
        return $formation->sections()->updateOrCreate(
            ['title' => $title],
            [
                'description' => 'Module '.$position.' du parcours professionnel.',
                'order_position' => $position,
                'duration' => $duration,
                'is_active' => true,
            ],
        );
    }

    /**
     * @param  array<int, array{0:string, 1:QuestionTypeEnum, 2:array<int,string>, 3:array<int,int>}>  $questions
     */
    private function exam(Formation|Section $parent, string $title, array $questions, int $passingScore = 70, int $maxAttempts = 3): void
    {
        $exam = $parent->exam()->updateOrCreate([], [
            'title' => $title,
            'description' => 'Évaluation obligatoire pour valider cette étape du parcours.',
            'instructions' => 'Répondez à toutes les questions. L’ordre change à chaque nouvelle tentative.',
            'duration_minutes' => 20,
            'passing_score' => $passingScore,
            'max_attempts' => $maxAttempts,
            'randomize_questions' => true,
            'show_results_immediately' => true,
            'is_active' => true,
            'available_from' => now()->subDay(),
            'available_until' => now()->addYears(2),
        ]);

        $exam->questions()->each(fn (Question $question) => $question->options()->delete());
        $exam->questions()->delete();

        foreach ($questions as $questionIndex => [$text, $type, $options, $correctIndexes]) {
            $question = $exam->questions()->create([
                'question_text' => $text,
                'question_type' => $type,
                'points' => 5,
                'order_position' => $questionIndex + 1,
                'explanation' => 'Consultez le support de la section pour revoir ce point.',
                'is_required' => true,
            ]);

            foreach ($options as $optionIndex => $option) {
                $question->options()->create([
                    'option_text' => $option,
                    'is_correct' => in_array($optionIndex, $correctIndexes, true),
                    'order_position' => $optionIndex + 1,
                ]);
            }
        }
    }

    private function pdfWorkbook(): string
    {
        return <<<'HTML'
<html><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#172033;font-size:12px;line-height:1.55}
h1{color:#8f2857} h2{margin-top:24px;color:#334155} table{width:100%;border-collapse:collapse}
th,td{border:1px solid #cbd5e1;padding:8px;text-align:left} th{background:#f1f5f9}
.formula{padding:12px;background:#f8fafc;border-left:4px solid #8f2857;font-family:monospace}
</style></head><body>
<h1>Registre professionnel des risques</h1>
<p>Ce support accompagne l’atelier de cartographie et sert de modèle de travail.</p>
<h2>Méthode de cotation</h2>
<div class="formula">Risque brut = Probabilité × Impact<br>Risque résiduel = Risque brut × (1 − Niveau de maîtrise)</div>
<h2>Exemple</h2>
<table><thead><tr><th>Événement</th><th>Cause</th><th>Impact</th><th>Score</th><th>Action</th></tr></thead>
<tbody><tr><td>Interruption du service</td><td>Panne réseau</td><td>Arrêt des opérations</td><td>16</td><td>Lien de secours</td></tr>
<tr><td>Erreur de paiement</td><td>Validation unique</td><td>Perte financière</td><td>12</td><td>Double validation</td></tr></tbody></table>
<h2>Questions d’atelier</h2><ol><li>Quel est le propriétaire du risque ?</li><li>Quel seuil déclenche l’escalade ?</li><li>Quelle preuve confirme l’efficacité du contrôle ?</li></ol>
</body></html>
HTML;
    }

    private function demoVideo(): string
    {
        return 'AAAAHGZ0eXBtcDQyAAAAAWlzb21tcDQxbXA0MgAAAAFtZGF0AAAAAAAAAOUAAAA2BgUtR1ZK3FxMQz+U78URPNFDqAEAAAMAAQMAAAMAAQIAAB9ACwAAAwAAAwAAAwBQDAORCACAAAAAVSW4IAGPEbm//zWkbUus+B0AqSb0yU9QAESIcHx6hglAtOBIJ3hFoIVZ2ABpzzWLB/vXl79D5JBUO7UblBhUTCz9JkA3DO98AD2Spn40n62Z8oDkREAAAAA+IeEEQt/+ePquP/iym/qlCVHnBHbOEoGmP6NbP//THvyIVONH9ZWO3Jz4cu1JZZT1UnmSKsdR7ABEOcxbN5AAAALIbW9vdgAAAGxtdmhkAAAAAOZ2bXHmdm1xAAACWAAABLAAAQAAAQAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAgAAAlR0cmFrAAAAXHRraGQAAAAB5nZtceZ2bXEAAAABAAAAAAAABLAAAAAAAAAAAAAAAAAAAAAAAAEAAAAAAAAAAAAAAAAAAAABAAAAAAAAAAAAAAAAAABAAAAAAEAAAAAkAAAAAAAkZWR0cwAAABxlbHN0AAAAAAAAAAEAAASwAAAAAAABAAAAAAHMbWRpYQAAACBtZGhkAAAAAOZ2bXHmdm1xAAACWAAABLBVxAAAAAAAMWhkbHIAAAAAAAAAAHZpZGUAAAAAAAAAAAAAAABDb3JlIE1lZGlhIFZpZGVvAAAAAXNtaW5mAAAAFHZtaGQAAAABAAAAAAAAAAAAAAAkZGluZgAAABxkcmVmAAAAAAAAAAEAAAAMdXJsIAAAAAEAAAEzc3RibAAAAKFzdHNkAAAAAAAAAAEAAACRYXZjMQAAAAAAAAABAAAAAAAAAAAAAAAAAAAAAABAACQASAAAAEgAAAAAAAAAAQAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAABj//wAAACdhdmNDAWQAC//hAAwnZAALrFZQw3gQYfQBAAQo7jyw/fj4AAAAAApmaWVsAQAAAAAKY2hybQAAAAAAGHN0dHMAAAAAAAAAAQAAAAIAAAJYAAAAFHN0c3MAAAAAAAAAAQAAAAEAAAAOc2R0cAAAAAAgEAAAABxzdHNjAAAAAAAAAAEAAAABAAAAAQAAAAEAAAAcc3RzegAAAAAAAAAAAAAAAgAAAJMAAABCAAAAGHN0Y28AAAAAAAAAAgAAACwAAAC/';
    }
}
