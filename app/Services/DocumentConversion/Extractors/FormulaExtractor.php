<?php

declare(strict_types=1);

namespace App\Services\DocumentConversion\Extractors;

use App\Contracts\ElementExtractorInterface;
use App\DTOs\DocumentElement;

/**
 * Extracteur de formules mathématiques
 */
final class FormulaExtractor implements ElementExtractorInterface
{
    public function extract(string $rawText, string $filePath, int &$position): array
    {
        $formulas = [];

        // Pattern pour LaTeX inline: $...$
        $inlinePattern = '/\$([^\$]+)\$/';
        // Pattern pour LaTeX block: $$...$$
        $blockPattern = '/\$\$(.+?)\$\$/s';

        // Extraire les formules block (priorité plus haute)
        if (preg_match_all($blockPattern, $rawText, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $index => $match) {
                $element = new DocumentElement(
                    type: 'formula',
                    content: $matches[1][$index][0],
                    position: $position++,
                    attributes: [
                        'display_mode' => 'block',
                        'raw' => $match[0],
                    ],
                );

                $formulas[] = $element;
            }
        }

        // Extraire les formules inline
        if (preg_match_all($inlinePattern, $rawText, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $index => $match) {
                // Ignorer si déjà capturé dans block
                $isInsideBlock = false;
                foreach ($formulas as $blockFormula) {
                    if ($blockFormula->getAttribute('display_mode') === 'block' &&
                        str_contains($blockFormula->getAttribute('raw'), $match[0])) {
                        $isInsideBlock = true;
                        break;
                    }
                }

                if (! $isInsideBlock) {
                    $element = new DocumentElement(
                        type: 'formula',
                        content: $matches[1][$index][0],
                        position: $position++,
                        attributes: [
                            'display_mode' => 'inline',
                            'raw' => $match[0],
                        ],
                    );

                    $formulas[] = $element;
                }
            }
        }

        return $formulas;
    }

    public function getElementType(): string
    {
        return 'formula';
    }
}
