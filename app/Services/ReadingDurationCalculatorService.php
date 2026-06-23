<?php

declare(strict_types=1);

namespace App\Services;

final class ReadingDurationCalculatorService
{
    private const array READING_SPEEDS = [
        'beginner' => 150,
        'average' => 200,
        'advanced' => 250,
        'professional' => 300,
    ];

    private const array ELEMENT_TIME = [
        'image' => 0.5,
        'table' => 1.0,
        'code_block' => 2.0,
        'diagram' => 1.5,
        'exercise' => 3.0,
    ];

    /**
     * Calcule la durée de lecture d'un contenu
     */
    public function calculateReadingDuration(
        string $content,
        string $level = 'average'
    ): array {
        $analysis = $this->analyzeContent($content);
        $baseReadingTime = $this->calculateBaseReadingTime($analysis['word_count'], $level);
        $complexityAdjustment = $this->calculateComplexityAdjustment($analysis, $baseReadingTime);
        $interactionTime = $this->calculateInteractionTime($analysis);

        $totalMinutes = $baseReadingTime + $complexityAdjustment + $interactionTime;

        $totalMinutes = ceil($totalMinutes);

        return [
            'total_minutes' => $totalMinutes,
            'breakdown' => [
                'base_reading' => round($baseReadingTime, 1),
                'complexity_adjustment' => round($complexityAdjustment, 1),
                'interaction_time' => round($interactionTime, 1),
            ],
            'analysis' => $analysis,
            'recommendations' => $this->generateRecommendations($totalMinutes, $analysis),
        ];
    }

    /**
     * Analyse le contenu pour extraire les métriques
     */
    private function analyzeContent(string $content): array
    {
        $cleanContent = $this->cleanContent($content);

        return [
            'word_count' => $this->countWords($cleanContent),
            'character_count' => mb_strlen($cleanContent),
            'paragraph_count' => $this->countParagraphs($content),
            'heading_count' => $this->countHeadings($content),
            'code_blocks' => $this->countCodeBlocks($content),
            'lists' => $this->countLists($content),
            'complexity_score' => $this->calculateComplexityScore($cleanContent),
            'technical_terms' => $this->countTechnicalTerms($cleanContent),
            'avg_sentence_length' => $this->calculateAverageSentenceLength($cleanContent),
            'reading_difficulty' => $this->assessReadingDifficulty($cleanContent),
        ];
    }

    /**
     * Nettoie le contenu (supprime markdown, HTML, etc.)
     */
    private function cleanContent(string $content): string
    {
        $content = preg_replace('/```[\s\S]*?```/', '', $content);
        $content = preg_replace('/`[^`]+`/', '', $content);
        $content = preg_replace('/!\[.*?\]\(.*?\)/', '', $content);
        $content = preg_replace('/\[.*?\]\(.*?\)/', '$1', $content);
        $content = preg_replace('/[#*_~`]/', '', $content);

        $content = strip_tags($content);

        $content = preg_replace('/\s+/', ' ', $content);

        return mb_trim($content);
    }

    /**
     * Compte les mots dans le contenu
     */
    private function countWords(string $content): int
    {
        if (empty(mb_trim($content))) {
            return 0;
        }

        return preg_match_all('/\b[\w\-àâäéèêëïîôöùûüÿç]+\b/u', $content);
    }

    /**
     * Compte les paragraphes
     */
    private function countParagraphs(string $content): int
    {
        return mb_substr_count($content, "\n\n") + 1;
    }

    /**
     * Compte les titres (H1, H2, H3, etc.)
     */
    private function countHeadings(string $content): int
    {
        return preg_match_all('/^#{1,6}\s+/m', $content);
    }

    /**
     * Compte les blocs de code
     */
    private function countCodeBlocks(string $content): int
    {
        return preg_match_all('/```[\s\S]*?```/', $content);
    }

    /**
     * Compte les listes
     */
    private function countLists(string $content): int
    {
        $bulletLists = preg_match_all('/^\s*[-*+]\s+/m', $content);
        $numberedLists = preg_match_all('/^\s*\d+\.\s+/m', $content);

        return $bulletLists + $numberedLists;
    }

    /**
     * Calcule un score de complexité
     */
    private function calculateComplexityScore(string $content): float
    {
        $score = 0;

        $words = preg_split('/\s+/', $content);
        $longWords = array_filter($words, fn ($word) => mb_strlen($word) > 7);
        $score += (count($longWords) / count($words)) * 100;

        $technicalPatterns = [
            '/\b(fonction|variable|classe|méthode|algorithme|database|serveur|API)\b/i',
            '/\b(développement|programmation|architecture|framework|library)\b/i',
            '/\b(SQL|HTML|CSS|JavaScript|PHP|Python|Laravel)\b/i',
        ];

        foreach ($technicalPatterns as $pattern) {
            $score += preg_match_all($pattern, $content) * 5;
        }

        return min($score, 100);
    }

    /**
     * Compte les termes techniques
     */
    private function countTechnicalTerms(string $content): int
    {
        $technicalPatterns = [
            '/\b(API|SQL|HTTP|JSON|XML|REST|CRUD)\b/',
            '/\b(class|function|method|variable|array|object)\b/i',
            '/\b(Laravel|PHP|JavaScript|Python|MySQL|PostgreSQL)\b/',
            '/\b(MVC|ORM|Framework|Library|Package)\b/i',
        ];

        $count = 0;
        foreach ($technicalPatterns as $pattern) {
            $count += preg_match_all($pattern, $content);
        }

        return $count;
    }

    /**
     * Calcule la longueur moyenne des phrases
     */
    private function calculateAverageSentenceLength(string $content): float
    {
        $sentences = preg_split('/[.!?]+/', $content);
        $sentences = array_filter($sentences, fn ($s) => mb_trim($s) !== '');

        if (empty($sentences)) {
            return 0;
        }

        $totalWords = 0;
        foreach ($sentences as $sentence) {
            $totalWords += $this->countWords($sentence);
        }

        return $totalWords / count($sentences);
    }

    /**
     * Évalue la difficulté de lecture
     */
    private function assessReadingDifficulty(string $content): string
    {
        $complexityScore = $this->calculateComplexityScore($content);

        if ($complexityScore > 70) {
            return 'very_hard';
        }
        if ($complexityScore > 50) {
            return 'hard';
        }
        if ($complexityScore > 30) {
            return 'medium';
        }

        return 'easy';

    }

    /**
     * Calcule le temps de lecture de base
     */
    private function calculateBaseReadingTime(int $wordCount, string $level): float
    {
        $wordsPerMinute = self::READING_SPEEDS[$level] ?? self::READING_SPEEDS['average'];

        return $wordCount / $wordsPerMinute;
    }

    /**
     * Calcule l'ajustement basé sur la complexité
     */
    private function calculateComplexityAdjustment(array $analysis, float $baseTime): float
    {
        $complexityFactor = 1.0;

        switch ($analysis['reading_difficulty']) {
            case 'very_hard':
                $complexityFactor *= 1.8;
                break;
            case 'hard':
                $complexityFactor *= 1.5;
                break;
            case 'medium':
                $complexityFactor *= 1.2;
                break;
            case 'easy':
                $complexityFactor *= 1.0;
                break;
        }

        if ($analysis['technical_terms'] > 10) {
            $complexityFactor *= 1.3;
        } elseif ($analysis['technical_terms'] > 5) {
            $complexityFactor *= 1.1;
        }

        if ($analysis['avg_sentence_length'] > 25) {
            $complexityFactor *= 1.2;
        }

        return $baseTime * ($complexityFactor - 1.0);
    }

    /**
     * Calcule le temps d'interaction (pauses, réflexion)
     */
    private function calculateInteractionTime(array $analysis): float
    {
        $interactionTime = 0;

        $interactionTime += $analysis['heading_count'] * 0.5;

        $interactionTime += $analysis['code_blocks'] * self::ELEMENT_TIME['code_block'];

        $interactionTime += $analysis['lists'] * 0.3;

        return $interactionTime;
    }

    /**
     * Génère des recommandations basées sur l'analyse
     */
    private function generateRecommendations(int $totalMinutes, array $analysis): array
    {
        $recommendations = [];

        if ($totalMinutes > 60) {
            $recommendations[] = "⚠️ Durée longue ({$totalMinutes} min) - Considérez diviser en plusieurs chapitres";
        }

        if ($analysis['reading_difficulty'] === 'very_hard') {
            $recommendations[] = '📚 Contenu très technique - Prévoir du temps supplémentaire pour la compréhension';
        }

        if ($analysis['code_blocks'] > 5) {
            $recommendations[] = '💻 Nombreux exemples de code - Les étudiants auront besoin de temps pour pratiquer';
        }

        if ($analysis['technical_terms'] > 15) {
            $recommendations[] = '🔧 Vocabulaire technique dense - Inclure un glossaire pourrait être utile';
        }

        if ($totalMinutes < 10) {
            $recommendations[] = '⚡ Chapitre court - Parfait pour une lecture rapide';
        }

        return $recommendations;
    }
}
