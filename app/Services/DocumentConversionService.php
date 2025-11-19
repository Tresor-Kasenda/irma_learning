<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ContentProcessorInterface;
use App\Contracts\DocumentExtractorInterface;
use App\DTOs\DocumentContent;
use App\Services\DocumentConversion\Extractors\PdfExtractor;
use App\Services\DocumentConversion\Processors\ContentStructureProcessor;
use App\Services\DocumentConversion\Processors\MarkdownEnhancementProcessor;
use App\Services\DocumentConversion\Processors\MarkdownLineBreakProcessor;
use App\Services\DocumentConversion\Processors\MarkdownProcessor;
use App\Services\DocumentConversion\Processors\PdfFormulaProcessor;
use App\Services\DocumentConversion\Processors\PdfTableProcessor;
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
     * @var array<ContentProcessorInterface>
     */
    private array $processors = [];

    public function __construct(
        private readonly PdfThumbnailService $thumbnailService,
        private readonly MarkdownFileService $markdownFileService
    )
    {
        $this->registerDefaultExtractors();
        $this->registerDefaultProcessors();
    }

    /**
     * Enregistre les extracteurs par défaut
     */
    private function registerDefaultExtractors(): void
    {
        $this->extractors = [
            new PdfExtractor,
        ];
    }

    /**
     * Enregistre les processors par défaut
     */
    private function registerDefaultProcessors(): void
    {
        $this->processors = [
            new PdfTableProcessor,            // Priorité 30 - Détecte les tableaux
            new PdfFormulaProcessor,          // Priorité 40 - Détecte les formules
            new MarkdownProcessor,            // Priorité 50 - Convertit en Markdown
            new MarkdownLineBreakProcessor,   // Priorité 55 - Assure les sauts de ligne corrects
            new ContentStructureProcessor,    // Priorité 60 - Structure le contenu
            new MarkdownEnhancementProcessor, // Priorité 70 - Améliore la qualité finale du Markdown
        ];

        usort($this->processors, fn($a, $b) => $a->getPriority() <=> $b->getPriority());
    }

    /**
     * Convertit un document en Markdown structuré
     *
     * @param array $options Options de conversion:
     *                          - generateThumbnail: bool (défaut: true)
     *                          - ignorePageNumbers: bool (défaut: true)
     *                          - skipFirstPage: bool (défaut: true)
     *                          - customTitle: string|null (titre personnalisé)
     *                          - customProcessors: array<ContentProcessorInterface>
     *
     * @throws Exception
     */
    public function convert(string $filePath, array $options = []): array
    {
        try {
            $options = array_merge([
                'generateThumbnail' => true,
                'ignorePageNumbers' => true,
                'skipFirstPage' => true,
                'customTitle' => null,
                'customProcessors' => [],
            ], $options);

            if (!file_exists($filePath)) {
                throw new Exception("Fichier non trouvé: {$filePath}");
            }

            $thumbnailPath = null;
            if ($options['generateThumbnail']) {
                $thumbnailPath = $this->thumbnailService->generateThumbnail($filePath);
            }

            $content = $this->extractDocument($filePath, $options);

            if (!empty($options['customTitle'])) {
                $content->metadata->title = $options['customTitle'];
            }

            $content = $this->processContent($content, $options);

            $markdownFilePath = $this->markdownFileService->saveMarkdownFile(
                $content->markdown,
                $content->metadata->title
            );

            return $this->formatResult($content, $thumbnailPath, $markdownFilePath);

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
     *
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
     * Traite le contenu avec les processors
     */
    private function processContent(DocumentContent $content, array $options): DocumentContent
    {
        foreach ($options['customProcessors'] ?? [] as $processor) {
            $this->registerProcessor($processor);
        }

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

        usort($this->processors, fn($a, $b) => $a->getPriority() <=> $b->getPriority());

        return $this;
    }

    /**
     * Formate le résultat final
     */
    private function formatResult(DocumentContent $content, ?string $thumbnailPath = null, ?string $markdownFilePath = null): array
    {
        return [
            'title' => $content->metadata->title ?? 'Document',
            'description' => $content->metadata->subject ?? 'Document extrait automatiquement',
            'content' => $content->markdown,
            'thumbnail_path' => $thumbnailPath,
            'markdown_file' => $markdownFilePath,
            'metadata' => [
                'document_info' => $content->metadata->toArray(),
                'statistics' => $content->getStats(),
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

        return max(5, (int)ceil($wordCount / 200));
    }
}
