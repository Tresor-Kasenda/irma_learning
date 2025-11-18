<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Processors;

use App\Contracts\ContentProcessorInterface;
use App\DTOs\DocumentContent;
use App\DTOs\DocumentElement;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Processeur pour détecter et formater les formules mathématiques
 */
final class PdfFormulaProcessor implements ContentProcessorInterface
{
    /**
     * Patterns pour détecter les formules mathématiques
     */
    private const array FORMULA_PATTERNS = [
        '/[∫∑∏√∂∇∆∈∉⊂⊃∪∩≈≠≤≥±×÷∞]/',
        '/\b\d+\s*\/\s*\d+\b/',
        '/[a-z]\^[0-9]+/',
        '/[a-z]_[0-9]+/',
        '/[a-zA-Z0-9\+\-\*\/\^\(\)]+\s*=\s*[a-zA-Z0-9\+\-\*\/\^\(\)]+/',
        '/\([a-zA-Z0-9\s\+\-\*\/\^]+\)/',
    ];

    private const array FORMULA_KEYWORDS = [
        'équation',
        'formule',
        'théorème',
        'lemme',
        'corollaire',
        'démonstration',
    ];

    public function process(DocumentContent $content): DocumentContent
    {
        try {
            $formulas = $this->detectFormulas($content->rawText);

            foreach ($formulas as $formula) {
                $content->addElement(new DocumentElement(
                    type: 'formula',
                    content: $formula['content'],
                    position: $formula['position'],
                    attributes: [
                        'display_mode' => $formula['display_mode'],
                        'confidence' => $formula['confidence'],
                    ]
                ));
            }

            if (count($formulas) > 0) {
                Log::info('Formulas detected in PDF content', [
                    'count' => count($formulas),
                ]);
            }
        } catch (Exception $e) {
            Log::warning('Failed to detect formulas', [
                'error' => $e->getMessage(),
            ]);
        }

        return $content;
    }

    /**
     * Détecte les formules mathématiques dans le texte
     */
    private function detectFormulas(string $text): array
    {
        $formulas = [];
        $lines = explode("\n", $text);
        $position = 0;

        foreach ($lines as $line) {
            $lineLength = mb_strlen($line) + 1;

            if ($this->containsFormula($line)) {
                $formula = $this->extractFormula($line, $position);
                if ($formula) {
                    $formulas[] = $formula;
                }
            }

            $position += $lineLength;
        }

        return $formulas;
    }

    /**
     * Vérifie si une ligne contient une formule mathématique
     */
    private function containsFormula(string $line): bool
    {
        $trimmed = mb_trim($line);

        if (empty($trimmed)) {
            return false;
        }

        foreach (self::FORMULA_PATTERNS as $pattern) {
            if (preg_match($pattern, $trimmed)) {
                return true;
            }
        }

        $lowerLine = mb_strtolower($trimmed);
        foreach (self::FORMULA_KEYWORDS as $keyword) {
            if (str_contains($lowerLine, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extrait la formule de la ligne
     */
    private function extractFormula(string $line, int $position): ?array
    {
        $trimmed = mb_trim($line);

        $confidence = $this->calculateConfidence($trimmed);

        if ($confidence < 0.3) {
            return null;
        }

        $displayMode = $this->determineDisplayMode($trimmed);

        $cleanedFormula = $this->cleanFormula($trimmed);

        return [
            'content' => $cleanedFormula,
            'position' => $position,
            'display_mode' => $displayMode,
            'confidence' => $confidence,
        ];
    }

    /**
     * Calcule un score de confiance pour une formule potentielle
     */
    private function calculateConfidence(string $text): float
    {
        $confidence = 0.0;
        $factors = 0;

        foreach (self::FORMULA_PATTERNS as $pattern) {
            if (preg_match($pattern, $text)) {
                $confidence += 0.3;
                $factors++;
            }
        }

        $lowerText = mb_strtolower($text);
        foreach (self::FORMULA_KEYWORDS as $keyword) {
            if (str_contains($lowerText, $keyword)) {
                $confidence += 0.2;
                $factors++;
            }
        }

        if (str_contains($text, '=')) {
            $confidence += 0.2;
            $factors++;
        }

        if ($this->hasBalancedParentheses($text)) {
            $confidence += 0.15;
            $factors++;
        }

        if ($factors > 0) {
            $confidence = min(1.0, $confidence);
        }

        return $confidence;
    }

    /**
     * Vérifie si les parenthèses sont équilibrées
     */
    private function hasBalancedParentheses(string $text): bool
    {
        $count = 0;
        $chars = mb_str_split($text);

        foreach ($chars as $char) {
            if ($char === '(') {
                $count++;
            } elseif ($char === ')') {
                $count--;
            }

            if ($count < 0) {
                return false;
            }
        }

        return $count === 0;
    }

    /**
     * Détermine le mode d'affichage de la formule
     */
    private function determineDisplayMode(string $text): string
    {
        $lowerText = mb_strtolower(mb_trim($text));

        foreach (self::FORMULA_KEYWORDS as $keyword) {
            if (str_starts_with($lowerText, $keyword)) {
                return 'block';
            }
        }

        if (mb_strlen($text) > 50) {
            return 'block';
        }

        return 'inline';
    }

    /**
     * Nettoie la formule pour l'affichage
     */
    private function cleanFormula(string $formula): string
    {
        foreach (self::FORMULA_KEYWORDS as $keyword) {
            $formula = preg_replace('/^' . preg_quote($keyword, '/') . '\s*:\s*/i', '', $formula);
        }

        $formula = preg_replace('/\s+/', ' ', $formula);

        return mb_trim($formula);
    }

    public function getPriority(): int
    {
        return 40; // Exécuté avant MarkdownProcessor (50)
    }
}
