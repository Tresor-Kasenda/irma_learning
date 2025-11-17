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
     * @param  string  $markdownContent  Contenu Markdown à sauvegarder
     * @param  string|null  $title  Titre du chapitre (optionnel, utilisé pour le nom du fichier)
     * @return string|null Chemin de stockage du fichier Markdown
     */
    public function saveMarkdownFile(string $markdownContent, ?string $title = null): ?string
    {
        try {
            // Générer le nom du fichier
            $filename = $this->generateFilename($title);
            $storagePath = self::STORAGE_PATH.'/'.$filename;

            // Sauvegarder le fichier
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
     * Supprime un fichier Markdown du stockage
     */
    public function deleteMarkdownFile(?string $filePath): bool
    {
        if (! $filePath) {
            return false;
        }

        try {
            if (Storage::disk('public')->exists($filePath)) {
                return Storage::disk('public')->delete($filePath);
            }

            return true;
        } catch (Exception $e) {
            Log::error('Erreur lors de la suppression du fichier Markdown', [
                'path' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Vérifie si un fichier Markdown existe
     */
    public function markdownFileExists(?string $filePath): bool
    {
        if (! $filePath) {
            return false;
        }

        return Storage::disk('public')->exists($filePath);
    }

    /**
     * Lit le contenu d'un fichier Markdown
     */
    public function readMarkdownFile(?string $filePath): ?string
    {
        if (! $filePath) {
            return null;
        }

        try {
            if (Storage::disk('public')->exists($filePath)) {
                return Storage::disk('public')->get($filePath);
            }

            return null;
        } catch (Exception $e) {
            Log::error('Erreur lors de la lecture du fichier Markdown', [
                'path' => $filePath,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Obtient l'URL publique du fichier Markdown
     */
    public function getMarkdownFileUrl(?string $filePath): ?string
    {
        if (! $filePath) {
            return null;
        }

        if (Storage::disk('public')->exists($filePath)) {
            return Storage::disk('public')->url($filePath);
        }

        return null;
    }

    /**
     * Génère un nom de fichier unique pour le Markdown
     */
    private function generateFilename(?string $title): string
    {
        if ($title) {
            // Nettoyer le titre pour en faire un nom de fichier valide
            $slug = Str::slug($title);
            $slug = mb_substr($slug, 0, 50); // Limiter à 50 caractères

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
