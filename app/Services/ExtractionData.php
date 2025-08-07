<?php

namespace App\Services;

use App\Contracts\PdfParserContact;
use Smalot\PdfParser\Parser;

class ExtractionData implements PdfParserContact
{
    public function __construct(
        public string $content
    )
    {
    }

    public function handle(): string
    {
        $parser = app(Parser::class);
        
        $fileContent = file_get_contents($this->content);

        $content = $parser
            ->parseFile($fileContent);

        return $content->getText();
    }
}
