<?php

declare(strict_types=1);

use App\Services\DocumentConversionService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->service = new DocumentConversionService;
    Storage::fake('local');
});

test('it can convert a PDF document to markdown', function () {
    // Créer un fichier PDF de test
    $pdfPath = __DIR__.'/../../Fixtures/sample.pdf';

    // Skip if test file doesn't exist
    if (! file_exists($pdfPath)) {
        $this->markTestSkipped('Sample PDF file not found');
    }

    $result = $this->service->convert($pdfPath);

    expect($result)->toBeArray()
        ->toHaveKeys(['title', 'description', 'content', 'metadata', 'estimated_duration'])
        ->and($result['content'])->toBeString()->not->toBeEmpty()
        ->and($result['metadata'])->toHaveKey('statistics')
        ->and($result['metadata']['statistics'])->toHaveKeys([
            'word_count',
            'elements_count',
            'images_count',
            'tables_count',
        ]);
});

test('it can extract images from PDF', function () {
    $pdfPath = __DIR__.'/../../Fixtures/sample_with_images.pdf';

    if (! file_exists($pdfPath)) {
        $this->markTestSkipped('Sample PDF file not found');
    }

    $result = $this->service->convert($pdfPath, [
        'extractImages' => true,
    ]);

    expect($result['metadata']['elements']['images'])->toBeArray();
});

test('it can extract tables from PDF', function () {
    $pdfPath = __DIR__.'/../../Fixtures/sample_with_tables.pdf';

    if (! file_exists($pdfPath)) {
        $this->markTestSkipped('Sample PDF file not found');
    }

    $result = $this->service->convert($pdfPath, [
        'extractTables' => true,
    ]);

    expect($result['metadata']['elements']['tables'])->toBeArray();
});

test('it generates table of contents', function () {
    $pdfPath = __DIR__.'/../../Fixtures/sample.pdf';

    if (! file_exists($pdfPath)) {
        $this->markTestSkipped('Sample PDF file not found');
    }

    $result = $this->service->convert($pdfPath, [
        'generateTOC' => true,
    ]);

    expect($result['metadata']['table_of_contents'])->toBeArray();
});

test('it can disable specific extractions', function () {
    $pdfPath = __DIR__.'/../../Fixtures/sample.pdf';

    if (! file_exists($pdfPath)) {
        $this->markTestSkipped('Sample PDF file not found');
    }

    $result = $this->service->convert($pdfPath, [
        'extractImages' => false,
        'extractTables' => false,
        'extractFormulas' => false,
    ]);

    expect($result['metadata']['statistics']['images_count'])->toBe(0)
        ->and($result['metadata']['statistics']['tables_count'])->toBe(0)
        ->and($result['metadata']['statistics']['formulas_count'])->toBe(0);
});

test('it throws exception for non-existent file', function () {
    $this->service->convert('/path/to/non-existent-file.pdf');
})->throws(Exception::class, 'Fichier non trouvé');

test('it throws exception for unsupported file type', function () {
    $invalidPath = __DIR__.'/../../Fixtures/sample.txt';

    if (! file_exists($invalidPath)) {
        // Create a dummy file for testing
        file_put_contents($invalidPath, 'Test content');
    }

    $this->service->convert($invalidPath);
})->throws(Exception::class);

test('it calculates estimated reading duration', function () {
    $pdfPath = __DIR__.'/../../Fixtures/sample.pdf';

    if (! file_exists($pdfPath)) {
        $this->markTestSkipped('Sample PDF file not found');
    }

    $result = $this->service->convert($pdfPath);

    expect($result['estimated_duration'])->toBeInt()
        ->toBeGreaterThanOrEqual(5); // Minimum 5 minutes
});
