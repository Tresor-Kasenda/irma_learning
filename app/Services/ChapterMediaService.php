<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Chapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

final class ChapterMediaService
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepare(array $data, ?Chapter $chapter = null): array
    {
        $type = (string) $data['content_type'];
        $video = $data['video'] ?? null;
        $media = $data['media'] ?? null;

        unset($data['video'], $data['media']);

        if ($type === 'text') {
            $data['content'] = $data['content'] ?? '';
            $this->clearMedia($chapter, $data);
            $this->clearProcessingState($data);

            return $data;
        }

        if ($type === 'video') {
            $data['content'] = '';
            $this->clearPdfMedia($chapter, $data);
            $this->clearProcessingState($data);

            if ($video instanceof UploadedFile) {
                $this->deleteFile($chapter?->video_url);
                $data['video_url'] = $video->store('chapters', 'public');
            }

            return $data;
        }

        $data['content'] = $data['content'] ?? $chapter?->content ?? '';
        $this->deleteFile($chapter?->video_url);
        $data['video_url'] = null;

        if ($media instanceof UploadedFile) {
            $this->deleteFile($chapter?->media_url);

            $data['media_url'] = $media->store('chapters', 'public');
            $data['cover_image'] = $chapter?->cover_image;
            $data['markdown_file'] = $chapter?->markdown_file;
            $data['processing_status'] = 'pending';
            $data['processing_error'] = null;
            $data['processing_metadata'] = $chapter?->processing_metadata;
            $data['processing_started_at'] = null;
            $data['processed_at'] = null;
        }

        return $data;
    }

    public function deleteChapterFiles(Chapter $chapter): void
    {
        $this->deleteFile($chapter->video_url);
        $this->deleteFile($chapter->media_url);
        $this->deleteExtractedAssets($chapter);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function clearMedia(?Chapter $chapter, array &$data): void
    {
        if ($chapter) {
            $this->deleteChapterFiles($chapter);
        }

        $data['video_url'] = null;
        $data['media_url'] = null;
        $data['cover_image'] = null;
        $data['markdown_file'] = null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function clearPdfMedia(?Chapter $chapter, array &$data): void
    {
        $this->deleteFile($chapter?->media_url);
        $this->deleteExtractedAssets($chapter);

        $data['media_url'] = null;
        $data['cover_image'] = null;
        $data['markdown_file'] = null;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function clearProcessingState(array &$data): void
    {
        $data['processing_status'] = null;
        $data['processing_error'] = null;
        $data['processing_metadata'] = null;
        $data['processing_started_at'] = null;
        $data['processed_at'] = null;
    }

    private function deleteExtractedAssets(?Chapter $chapter): void
    {
        if (! $chapter) {
            return;
        }

        $processingMetadata = $chapter->processing_metadata ?? [];
        $retainedAssetDirectories = is_array($processingMetadata['previous_asset_directories'] ?? null)
            ? $processingMetadata['previous_asset_directories']
            : [];
        $assetDirectories = [
            $processingMetadata['asset_directory'] ?? null,
            $processingMetadata['previous_asset_directory'] ?? null,
            ...$retainedAssetDirectories,
        ];

        foreach ($assetDirectories as $assetDirectory) {
            if (is_string($assetDirectory) && $assetDirectory !== '') {
                Storage::disk('public')->deleteDirectory($assetDirectory);
            }
        }

        $this->deleteFile($chapter->cover_image);
        $this->deleteFile($chapter->markdown_file);
    }

    private function deleteFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
