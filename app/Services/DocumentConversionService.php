<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ContentProcessorInterface;
use App\Contracts\DocumentExtractorInterface;
use App\Contracts\ElementExtractorInterface;
use App\DTOs\DocumentContent;
use App\Services\DocumentConversion\Extractors\CodeBlockExtractor;
use App\Services\DocumentConversion\Extractors\FormulaExtractor;
use App\Services\DocumentConversion\Extractors\ImageExtractor;
use App\Services\DocumentConversion\Extractors\PdfExtractor;
use App\Services\DocumentConversion\Extractors\TableExtractor;
use App\Services\DocumentConversion\Processors\MarkdownProcessor;
use App\Services\DocumentConversion\Processors\TableOfContentsProcessor;
use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Service principal pour la conversion de documents
 *
 * Architecture:
 * - Utilise le Strategy Pattern pour les extractors
 * - Utilise le Chain of Responsibility Pattern pour les processors
 * - Suit les principes SOLID (Single Responsibility, Open/Closed, etc.)
 */
final class DocumentConversionService
{
    /**
     * @var array<DocumentExtractorInterface>
     */
    private array $extractors = [];

    /**
     * @var array<ElementExtractorInterface>
     */
    private array $elementExtractors = [];

    /**
     * @var array<ContentProcessorInterface>
     */
    private array $processors = [];

    public function __construct()
    {
        $this->registerDefaultExtractors();
        $this->registerDefaultElementExtractors();
        $this->registerDefaultProcessors();
    }

    /**
     * Enregistre les extracteurs par défaut
     */
    private function registerDefaultExtractors(): void
    {
        $this->extractors = [
            new PdfExtractor,
            // Ajouter d'autres extracteurs ici (Word, etc.)
        ];
    }

    /**
     * Enregistre les extracteurs d'éléments par défaut
     */
    private function registerDefaultElementExtractors(): void
    {
        $this->elementExtractors = [
            new ImageExtractor,
            new TableExtractor,
            new FormulaExtractor,
            new CodeBlockExtractor,
        ];
    }

    /**
     * Enregistre les processors par défaut
     */
    private function registerDefaultProcessors(): void
    {
        $this->processors = [
            new MarkdownProcessor,
            new TableOfContentsProcessor,
        ];

        // Trier par priorité
        usort($this->processors, fn($a, $b) => $a->getPriority() <=> $b->getPriority());
    }

    /**
     * Convertit un document en Markdown structuré
     *
     * @param array $options Options de conversion:
     *                          - extractImages: bool (défaut: true)
     *                          - extractTables: bool (défaut: true)
     *                          - extractFormulas: bool (défaut: true)
     *                          - extractCode: bool (défaut: true)
     *                          - generateTOC: bool (défaut: true)
     *                          - ignorePageNumbers: bool (défaut: true)
     *                          - customExtractors: array<ElementExtractorInterface>
     *                          - customProcessors: array<ContentProcessorInterface>
     */
    public function convert(string $filePath, array $options = []): array
    {
        try {
            // Options par défaut
            $options = array_merge([
                'extractImages' => true,
                'extractTables' => true,
                'extractFormulas' => true,
                'extractCode' => true,
                'generateTOC' => true,
                'ignorePageNumbers' => true,
                'customExtractors' => [],
                'customProcessors' => [],
            ], $options);

            // Vérifier que le fichier existe
            if (!file_exists($filePath)) {
                throw new Exception("Fichier non trouvé: {$filePath}");
            }

            // 1. Extraire le contenu brut du document
            $content = $this->extractDocument($filePath, $options);

            // 2. Extraire les éléments spéciaux (images, tableaux, etc.)
            $content = $this->extractElements($content, $filePath, $options);

            // 3. Traiter le contenu (conversion en Markdown, TOC, etc.)
            $content = $this->processContent($content, $options);

            // 4. Retourner le résultat formaté
            return $this->formatResult($content);

        } catch (Exception $e) {
            Log::error('Document conversion failed', [
                'file' => $filePath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw new Exception("Échec de la conversion du document: {$e->getMessage()}");
        }
    }

    /**
     * Extrait le document selon son type
     * @throws Exception
     */
    private function extractDocument(string $filePath, array $options): DocumentContent
    {
        foreach ($this->extractors as $extractor) {
            if ($extractor->supports($filePath)) {
                Log::info('Using extractor', ['extractor' => get_class($extractor)]);

                return $extractor->extract($filePath, $options);
            }
        }

        throw new Exception('Aucun extracteur compatible trouvé pour ce type de fichier');
    }

    /**
     * Extrait les éléments spéciaux du contenu
     */
    private function extractElements(DocumentContent $content, string $filePath, array $options): DocumentContent
    {
        $position = 0;

        foreach ($this->elementExtractors as $extractor) {
            $elementType = $extractor->getElementType();

            // Vérifier si l'extraction de ce type est activée
            $optionKey = 'extract' . ucfirst($elementType) . 's';
            if (isset($options[$optionKey]) && !$options[$optionKey]) {
                continue;
            }

            try {
                $elements = $extractor->extract($content->rawText, $filePath, $position);

                foreach ($elements as $element) {
                    $content->addElement($element);
                }

                Log::debug('Extracted elements', [
                    'type' => $elementType,
                    'count' => count($elements),
                ]);
            } catch (Exception $e) {
                Log::warning('Element extraction failed', [
                    'type' => $elementType,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $content;
    }

    /**
     * Traite le contenu avec les processors
     */
    private function processContent(DocumentContent $content, array $options): DocumentContent
    {
        // Ajouter les processors personnalisés
        foreach ($options['customProcessors'] ?? [] as $processor) {
            $this->registerProcessor($processor);
        }

        // Exécuter les processors dans l'ordre de priorité
        foreach ($this->processors as $processor) {
            try {
                $content = $processor->process($content);

                Log::debug('Content processed', [
                    'processor' => get_class($processor),
                ]);
            } catch (Exception $e) {
                Log::warning('Content processing failed', [
                    'processor' => get_class($processor),
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $content;
    }

    /**
     * Enregistre un processeur de contenu personnalisé
     */
    public function registerProcessor(ContentProcessorInterface $processor): self
    {
        $this->processors[] = $processor;

        // Trier par priorité
        usort($this->processors, fn($a, $b) => $a->getPriority() <=> $b->getPriority());

        return $this;
    }

    /**
     * Formate le résultat final
     */
    private function formatResult(DocumentContent $content): array
    {
        return [
            'title' => $content->metadata->title ?? 'Document',
            'description' => $content->metadata->subject ?? 'Document extrait automatiquement',
            'content' => $content->markdown,
            'metadata' => [
                'document_info' => $content->metadata->toArray(),
                'statistics' => $content->getStats(),
                'table_of_contents' => $content->tableOfContents->toArray(),
                'elements' => [
                    'images' => $content->getElementsByType('image')->map->toArray()->values()->toArray(),
                    'tables' => $content->getElementsByType('table')->map->toArray()->values()->toArray(),
                    'formulas' => $content->getElementsByType('formula')->map->toArray()->values()->toArray(),
                    'code_blocks' => $content->getElementsByType('code')->map->toArray()->values()->toArray(),
                ],
            ],
            'estimated_duration' => $this->calculateEstimatedDuration($content),
        ];
    }

    /**
     * Calcule la durée estimée de lecture
     */
    private function calculateEstimatedDuration(DocumentContent $content): int
    {
        $wordCount = str_word_count(strip_tags($content->markdown));

        // Moyenne de 200 mots par minute
        return max(5, (int)ceil($wordCount / 200));
    }

    /**
     * Enregistre un extracteur de document personnalisé
     */
    public function registerExtractor(DocumentExtractorInterface $extractor): self
    {
        $this->extractors[] = $extractor;

        return $this;
    }

    /**
     * Enregistre un extracteur d'éléments personnalisé
     */
    public function registerElementExtractor(ElementExtractorInterface $extractor): self
    {
        $this->elementExtractors[] = $extractor;

        return $this;
    }
}
