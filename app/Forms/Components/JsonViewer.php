<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Concerns\CanBeDisabled;
use Filament\Forms\Components\Field;

class JsonViewer extends Field
{
    use CanBeDisabled;

    protected string $view = 'forms.components.json-viewer';

    protected bool $isCollapsible = true;
    protected bool $isDefaultCollapsed = false;
    protected int $maxDepth = 10;
    protected array $hiddenKeys = [] {
        get {
            return $this->hiddenKeys;
        }
    }
    protected bool $showTypes = true;
    protected string $theme = 'light' {
        get {
            return $this->theme;
        }
        set(string $value) {
            $this->theme = in_array($value, ['light', 'dark']) ? $value : 'light';
        }
    }
    protected bool $showToolbar = true;
    protected bool $enableSearch = false;
    protected bool $showLineNumbers = false;
    protected array $highlights = [];

    public static function make(string $name): static
    {
        return app(static::class, ['name' => $name]);
    }

    public function collapsible(bool $condition = true): static
    {
        $this->isCollapsible = $condition;
        return $this;
    }

    public function defaultCollapsed(bool $condition = true): static
    {
        $this->isDefaultCollapsed = $condition;
        return $this;
    }

    public function maxDepth(int $depth): static
    {
        $this->maxDepth = $depth;
        return $this;
    }

    public function hideKeys(array $keys): static
    {
        $this->hiddenKeys = $keys;
        return $this;
    }

    public function showTypes(bool $condition = true): static
    {
        $this->showTypes = $condition;
        return $this;
    }

    public function theme(string $theme): static
    {
        $this->theme = in_array($theme, ['light', 'dark']) ? $theme : 'light';
        return $this;
    }

    public function toolbar(bool $condition = true): static
    {
        $this->showToolbar = $condition;
        return $this;
    }

    public function searchable(bool $condition = true): static
    {
        $this->enableSearch = $condition;
        return $this;
    }

    public function lineNumbers(bool $condition = true): static
    {
        $this->showLineNumbers = $condition;
        return $this;
    }

    public function highlight(array $keys): static
    {
        $this->highlights = $keys;
        return $this;
    }

    // Getters
    public function isCollapsible(): bool
    {
        return $this->isCollapsible;
    }

    public function isDefaultCollapsed(): bool
    {
        return $this->isDefaultCollapsed;
    }

    public function getMaxDepth(): int
    {
        return $this->maxDepth;
    }

    public function shouldShowTypes(): bool
    {
        return $this->showTypes;
    }

    public function hasToolbar(): bool
    {
        return $this->showToolbar;
    }

    public function isSearchable(): bool
    {
        return $this->enableSearch;
    }

    public function hasLineNumbers(): bool
    {
        return $this->showLineNumbers;
    }

    public function getHighlights(): array
    {
        return $this->highlights;
    }

    public function processData($data, int $currentDepth = 0, string $path = ''): array
    {
        if ($currentDepth >= $this->maxDepth) {
            return [
                'type' => 'truncated',
                'value' => '... (profondeur maximale atteinte)',
                'path' => $path
            ];
        }

        if (is_null($data)) {
            return ['type' => 'null', 'value' => 'null', 'path' => $path];
        }

        if (is_bool($data)) {
            return [
                'type' => 'boolean',
                'value' => $data ? 'true' : 'false',
                'path' => $path
            ];
        }

        if (is_numeric($data)) {
            return [
                'type' => is_int($data) ? 'integer' : 'float',
                'value' => $data,
                'path' => $path,
                'formatted' => $this->formatNumber($data)
            ];
        }

        if (is_string($data)) {
            return [
                'type' => 'string',
                'value' => $data,
                'path' => $path,
                'length' => strlen($data),
                'preview' => $this->getStringPreview($data)
            ];
        }

        if (is_array($data)) {
            $processedArray = [];
            $isAssociative = array_keys($data) !== range(0, count($data) - 1);

            foreach ($data as $key => $value) {
                if (in_array($key, $this->hiddenKeys)) {
                    continue;
                }

                $newPath = $path ? "{$path}.{$key}" : (string)$key;
                $processedArray[$key] = $this->processData($value, $currentDepth + 1, $newPath);
            }

            return [
                'type' => $isAssociative ? 'object' : 'array',
                'value' => $processedArray,
                'count' => count($processedArray),
                'path' => $path,
                'is_highlighted' => in_array($path, $this->highlights)
            ];
        }

        if (is_object($data)) {
            $processedObject = [];
            $className = get_class($data);

            // Conversion en array pour l'affichage
            if (method_exists($data, 'toArray')) {
                $arrayData = $data->toArray();
            } else {
                $arrayData = (array)$data;
            }

            foreach ($arrayData as $key => $value) {
                if (in_array($key, $this->hiddenKeys)) {
                    continue;
                }

                $newPath = $path ? "{$path}.{$key}" : $key;
                $processedObject[$key] = $this->processData($value, $currentDepth + 1, $newPath);
            }

            return [
                'type' => 'object',
                'value' => $processedObject,
                'class' => $className,
                'count' => count($processedObject),
                'path' => $path,
                'is_highlighted' => in_array($path, $this->highlights)
            ];
        }

        return [
            'type' => 'unknown',
            'value' => (string)$data,
            'path' => $path
        ];
    }

    private function formatNumber($number): string
    {
        if (is_float($number)) {
            return number_format($number, 2, ',', ' ');
        }

        if (is_int($number) && $number >= 1000) {
            return number_format($number, 0, ',', ' ');
        }

        return (string)$number;
    }

    private function getStringPreview(string $data): string
    {
        if (strlen($data) <= 100) {
            return $data;
        }

        return substr($data, 0, 97) . '...';
    }

    public function getStatsForData($data): array
    {
        $stats = [
            'total_keys' => 0,
            'max_depth' => 0,
            'types' => [],
            'size_estimate' => 0
        ];

        $this->collectStats($data, $stats, 0);

        return [
            'total_keys' => $stats['total_keys'],
            'max_depth' => $stats['max_depth'],
            'types' => array_count_values($stats['types']),
            'size_estimate' => $this->formatBytes($stats['size_estimate'])
        ];
    }

    private function collectStats($data, array &$stats, int $depth): void
    {
        $stats['max_depth'] = max($stats['max_depth'], $depth);

        if (is_array($data)) {
            $stats['total_keys'] += count($data);
            $stats['types'][] = 'array';
            $stats['size_estimate'] += strlen(json_encode($data));

            foreach ($data as $value) {
                $this->collectStats($value, $stats, $depth + 1);
            }
        } elseif (is_object($data)) {
            $arrayData = method_exists($data, 'toArray') ? $data->toArray() : (array)$data;
            $stats['total_keys'] += count($arrayData);
            $stats['types'][] = 'object';
            $stats['size_estimate'] += strlen(json_encode($arrayData));

            foreach ($arrayData as $value) {
                $this->collectStats($value, $stats, $depth + 1);
            }
        } else {
            $stats['types'][] = gettype($data);
            $stats['size_estimate'] += strlen((string)$data);
        }
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $factor = floor((strlen($bytes) - 1) / 3);

        return sprintf("%.1f", $bytes / pow(1024, $factor)) . ' ' . $units[$factor];
    }
}
