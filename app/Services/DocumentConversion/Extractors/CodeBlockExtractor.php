<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Extractors;

use App\Contracts\ElementExtractorInterface;
use App\DTOs\DocumentElement;

/**
 * Extracteur de blocs de code
 */
final class CodeBlockExtractor implements ElementExtractorInterface
{
    private const array CODE_PATTERNS = [
        // Patterns stricts
        '/^(function|def|class|public|private|protected)\s+/',
        '/^(if|else|for|while|switch|case)\s*\(/',
        '/^(import|include|require|from)\s+/',
        '/^(var|let|const|int|string|bool|float|double)\s+/',
        '/^\$[a-zA-Z_][a-zA-Z0-9_]*\s*=/',
        '/^console\.(log|error|warn|info)/',
        '/^#include|^#define/',
        '/^@(Override|Test|Before|After)/',
    ];

    public function extract(string $rawText, string $filePath, int &$position): array
    {
        $codeBlocks = [];
        $lines = explode("\n", $rawText);

        $inCodeBlock = false;
        $codeBuffer = [];
        $blockStartPosition = 0;

        foreach ($lines as $lineNumber => $line) {
            $trimmedLine = mb_trim($line);

            // Détecte le début d'un bloc de code
            if (!$inCodeBlock && $this->isCodeLine($trimmedLine)) {
                $inCodeBlock = true;
                $blockStartPosition = $position++;
                $codeBuffer = [$line];

                continue;
            }

            // Dans un bloc de code
            if ($inCodeBlock) {
                $codeBuffer[] = $line;

                // Détecte la fin du bloc de code
                if ($this->isEndOfCodeBlock($trimmedLine, $codeBuffer)) {
                    $element = $this->createCodeBlockElement($codeBuffer, $blockStartPosition);
                    $codeBlocks[] = $element;

                    $inCodeBlock = false;
                    $codeBuffer = [];
                }
            }
        }

        // Si le document se termine avec un bloc de code
        if ($inCodeBlock && !empty($codeBuffer)) {
            $element = $this->createCodeBlockElement($codeBuffer, $blockStartPosition);
            $codeBlocks[] = $element;
        }

        return $codeBlocks;
    }

    /**
     * Vérifie si la ligne est du code
     */
    private function isCodeLine(string $line): bool
    {
        foreach (self::CODE_PATTERNS as $pattern) {
            if (preg_match($pattern, $line)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Détecte la fin d'un bloc de code
     */
    private function isEndOfCodeBlock(string $line, array $codeBuffer): bool
    {
        // Si vide ou ligne simple, potentiellement la fin
        if (empty($line)) {
            return count($codeBuffer) > 3; // Au moins 3 lignes de code
        }

        // Si 2 lignes consécutives non-code, c'est la fin
        $recentLines = array_slice($codeBuffer, -3);
        $nonCodeLines = 0;

        foreach ($recentLines as $recentLine) {
            if (!$this->isCodeLine(mb_trim($recentLine)) && !empty(mb_trim($recentLine))) {
                $nonCodeLines++;
            }
        }

        return $nonCodeLines >= 2;
    }

    /**
     * Crée un élément de bloc de code
     */
    private function createCodeBlockElement(array $codeLines, int $position): DocumentElement
    {
        $code = implode("\n", $codeLines);
        $language = $this->detectLanguage($code);

        return new DocumentElement(
            type: 'code',
            content: $code,
            position: $position,
            attributes: [
                'language' => $language,
                'line_count' => count($codeLines),
            ],
        );
    }

    /**
     * Détecte le langage de programmation
     */
    private function detectLanguage(string $code): string
    {
        $languages = [
            'php' => '/\$[a-zA-Z_]|<\?php|namespace\s+/',
            'javascript' => '/function\s*\(|console\.|var\s+|let\s+|const\s+|=>/',
            'typescript' => '/interface\s+|type\s+|:.*=|<.*>/',
            'python' => '/def\s+|import\s+|print\s*\(|class\s+.*:/',
            'java' => '/public\s+class|import\s+java|@Override/',
            'kotlin' => '/fun\s+|var\s+|val\s+|class\s+/',
            'swift' => '/func\s+|var\s+|let\s+|struct\s+/',
            'dart' => '/class\s+|abstract\s+|enum\s+|void\s+main/',
            'c' => '/#include|int\s+main|printf\s*\(/',
            'cpp' => '/#include|using\s+namespace|cout\s*<</',
            'csharp' => '/using\s+System|public\s+class|namespace\s+/',
            'go' => '/func\s+|package\s+|import\s+\(/',
            'rust' => '/fn\s+|let\s+mut|use\s+std/',
            'sql' => '/SELECT\s+|INSERT\s+|UPDATE\s+|DELETE\s+/i',
            'html' => '/<[a-z]+|<\/[a-z]+>/i',
            'css' => '/\{[^}]*\}|@media/',
            'json' => '/^\s*[\{\[]/m',
        ];

        foreach ($languages as $lang => $pattern) {
            if (preg_match($pattern, $code)) {
                return $lang;
            }
        }

        return 'text';
    }

    public function getElementType(): string
    {
        return 'code';
    }
}
