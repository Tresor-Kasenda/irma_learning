<?php

declare(strict_types=1);

use App\Services\MarkdownToHtmlConverter;

test('markdown rendering preserves language hooks and math delimiters for client enhancement', function () {
    $markdown = <<<'MARKDOWN'
```python
print('IRMA')
```

```mermaid
flowchart LR
    A[Début] --> B[Fin]
```

$$
E = mc^2
$$
MARKDOWN;

    $html = app(MarkdownToHtmlConverter::class)->convert($markdown);

    expect($html)
        ->toContain('language-python')
        ->toContain('language-mermaid')
        ->toContain('E = mc^2')
        ->toContain('$$');
});
