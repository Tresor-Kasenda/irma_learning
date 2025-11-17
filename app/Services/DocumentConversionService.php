<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ContentProcessorInterface;
use App\Contracts\DocumentExtractorInterface;
use App\DTOs\DocumentContent;
use App\Services\DocumentConversion\Extractors\PdfExtractor;
use App\Services\DocumentConversion\Processors\ContentStructureProcessor;
use App\Services\DocumentConversion\Processors\MarkdownProcessor;
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
        private readonly PdfThumbnailService $thumbnailService
    ) {
        $this->registerDefaultExtractors();
        $this->registerDefaultProcessors();
    }

    /**
     * Convertit un document en Markdown structuré
     *
     * @param  array  $options  Options de conversion:
     *                          - generateThumbnail: bool (défaut: true)
     *                          - ignorePageNumbers: bool (défaut: true)
     *                          - customProcessors: array<ContentProcessorInterface>
     */
    public function convert(string $filePath, array $options = []): array
    {
        try {
            // Options par défaut
            $options = array_merge([
                'generateThumbnail' => true,
                'ignorePageNumbers' => true,
                'customProcessors' => [],
            ], $options);

            // Vérifier que le fichier existe
            if (! file_exists($filePath)) {
                throw new Exception("Fichier non trouvé: {$filePath}");
            }

            // 1. Générer la miniature de la première page
            $thumbnailPath = null;
            if ($options['generateThumbnail']) {
                $thumbnailPath = $this->thumbnailService->generateThumbnail($filePath);
            }

            // 2. Extraire le contenu brut du document
            $content = $this->extractDocument($filePath, $options);

            // 3. Traiter le contenu (conversion en Markdown, structuration)
            $content = $this->processContent($content, $options);

            // 4. Retourner le résultat formaté
            return $this->formatResult($content, $thumbnailPath);

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
     * Enregistre un processeur de contenu personnalisé
     */
    public function registerProcessor(ContentProcessorInterface $processor): self
    {
        $this->processors[] = $processor;

        // Trier par priorité
        usort($this->processors, fn ($a, $b) => $a->getPriority() <=> $b->getPriority());

        return $this;
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
     * Enregistre les processors par défaut
     */
    private function registerDefaultProcessors(): void
    {
        $this->processors = [
            new MarkdownProcessor,
            new ContentStructureProcessor,
        ];

        // Trier par priorité
        usort($this->processors, fn ($a, $b) => $a->getPriority() <=> $b->getPriority());
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
     * Formate le résultat final
     */
    private function formatResult(DocumentContent $content, ?string $thumbnailPath = null): array
    {
        return [
            'title' => $content->metadata->title ?? 'Document',
            'description' => $content->metadata->subject ?? 'Document extrait automatiquement',
            'content' => $content->markdown,
            'thumbnail_path' => $thumbnailPath,
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

        // Moyenne de 200 mots par minute
        return max(5, (int) ceil($wordCount / 200));
    }
}
