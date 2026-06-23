<?php

declare(strict_types=1);

use App\Services\DocumentConversionService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->service = app(DocumentConversionService::class);
    Storage::fake('public');
});

test('it can convert a PDF document to markdown', function () {
    $pdfPath = __DIR__.'/../../Fixtures/sample.pdf';

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
        ->toBeGreaterThanOrEqual(5);
});

// --- Tests encodage et ponctuation ---

test('isImportantShortLine preserves punctuation on short lines', function () {
    $extractor = new App\Services\DocumentConversion\Extractors\PdfExtractor;
    $reflection = new ReflectionClass($extractor);
    $method = $reflection->getMethod('isImportantShortLine');
    $method->setAccessible(true);

    // Ponctuation seule doit être conservée
    expect($method->invoke($extractor, ','))->toBeTrue()
        ->and($method->invoke($extractor, ':'))->toBeTrue()
        ->and($method->invoke($extractor, '!'))->toBeTrue()
        ->and($method->invoke($extractor, '?'))->toBeTrue()
        ->and($method->invoke($extractor, ';'))->toBeTrue()
        // Les chiffres seuls (numéros de page) restent filtrés
        ->and($method->invoke($extractor, '5'))->toBeFalse()
        ->and($method->invoke($extractor, '12'))->toBeFalse();
});

test('isPageNumber does not filter decimal numbers', function () {
    $extractor = new App\Services\DocumentConversion\Extractors\PdfExtractor;
    $reflection = new ReflectionClass($extractor);
    $method = $reflection->getMethod('isPageNumber');
    $method->setAccessible(true);

    // Entiers courts → numéros de page → filtrés
    expect($method->invoke($extractor, '5'))->toBeTrue()
        ->and($method->invoke($extractor, '42'))->toBeTrue()
        // Décimaux → contenu légitime → PAS filtrés
        ->and($method->invoke($extractor, '1.5'))->toBeFalse()
        ->and($method->invoke($extractor, '3.14'))->toBeFalse()
        ->and($method->invoke($extractor, '99.9'))->toBeFalse();
});

test('fixEncodingIssues removes null bytes', function () {
    $extractor = new App\Services\DocumentConversion\Extractors\PdfExtractor;
    $reflection = new ReflectionClass($extractor);
    $method = $reflection->getMethod('fixEncodingIssues');
    $method->setAccessible(true);

    $textWithNulls = "Hel\x00lo\x00 \x00World";
    $result = $method->invoke($extractor, $textWithNulls);

    expect($result)->toBe('Hello World')
        ->not->toContain("\x00");
});

test('fixEncodingIssues converts ellipsis and low quotes', function () {
    $extractor = new App\Services\DocumentConversion\Extractors\PdfExtractor;
    $reflection = new ReflectionClass($extractor);
    $method = $reflection->getMethod('fixEncodingIssues');
    $method->setAccessible(true);

    // Ellipse → trois points ASCII
    expect($method->invoke($extractor, "Suite\xE2\x80\xA6"))->toBe('Suite...')
        // Virgule basse → virgule ASCII
        ->and($method->invoke($extractor, "Texte\xE2\x80\x9Asuite"))->toBe('Texte,suite')
        // Guillemet bas double → guillemet ASCII
        ->and($method->invoke($extractor, "\xE2\x80\x9EBonjour\""))->toBe('"Bonjour"');
});

test('joinParagraphLines does not add space before punctuation', function () {
    $processor = new App\Services\DocumentConversion\Processors\MarkdownProcessor;
    $reflection = new ReflectionClass($processor);
    $method = $reflection->getMethod('joinParagraphLines');
    $method->setAccessible(true);

    // Virgule après mot → pas d'espace avant la virgule
    expect($method->invoke($processor, ['Bonjour', ',', 'comment']))->toBe('Bonjour, comment')
        // Point d'interrogation → pas d'espace
        ->and($method->invoke($processor, ['Vraiment', '?']))->toBe('Vraiment?')
        // Deux-points → pas d'espace
        ->and($method->invoke($processor, ['Résultat', ':', 'succès']))->toBe('Résultat: succès')
        // Jointure normale entre mots → espace conservé
        ->and($method->invoke($processor, ['Bonjour', 'le', 'monde']))->toBe('Bonjour le monde');
});
