<?php

declare(strict_types=1);

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service pour gérer la sauvegarde des fichiers Markdown
 */
final class MarkdownFileService
{
    private const string STORAGE_PATH = 'chapters/markdown';

    /**
     * Sauvegarde le contenu Markdown dans un fichier
     *
     * @param string $markdownContent Contenu Markdown à sauvegarder
     * @param string|null $title Titre du chapitre (optionnel, utilisé pour le nom du fichier)
     * @return string|null Chemin de stockage du fichier Markdown
     */
    public function saveMarkdownFile(string $markdownContent, ?string $title = null): ?string
    {
        try {
            $filename = $this->generateFilename($title);
            $storagePath = self::STORAGE_PATH . '/' . $filename;

            Storage::disk('public')->put($storagePath, $markdownContent);

            Log::info('Fichier Markdown sauvegardé avec succès', [
                'path' => $storagePath,
                'size' => mb_strlen($markdownContent),
            ]);

            return $storagePath;

        } catch (Exception $e) {
            Log::error('Erreur lors de la sauvegarde du fichier Markdown', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return null;
        }
    }

    /**
     * Génère un nom de fichier unique pour le Markdown
     */
    private function generateFilename(?string $title): string
    {
        if ($title) {
            $slug = Str::slug($title);
            $slug = mb_substr($slug, 0, 50);

            return sprintf(
                '%s_%s.md',
                $slug,
                uniqid('', true)
            );
        }

        return sprintf(
            'chapter_%s.md',
            uniqid('', true)
        );
    }
}
