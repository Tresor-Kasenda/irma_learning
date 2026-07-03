#!/usr/bin/env python3

import argparse
import json
from pathlib import Path

parser = argparse.ArgumentParser()
parser.add_argument("--input", required=True)
parser.add_argument("--output-dir", required=True, type=Path)
parser.add_argument("--public-prefix", required=True)
parser.add_argument("--max-pages")
parser.add_argument("--image-dpi")
parser.add_argument("--visual-dpi")
parser.add_argument("--ocr-language")
parser.add_argument("--batch-size")
parser.add_argument("--parallel")
arguments, _ = parser.parse_known_args()

arguments.output_dir.mkdir(parents=True, exist_ok=True)
(arguments.output_dir / "cover.png").write_bytes(b"fake-cover")
(arguments.output_dir / "page-0001.png").write_bytes(b"fake-page")

print(
    json.dumps(
        {
            "markdown": "# Contenu Python\n\nUne équation $E = mc^2$.\n\n![Page]({}/page-0001.png)".format(
                arguments.public_prefix.rstrip("/")
            ),
            "page_count": 1,
            "word_count": 8,
            "image_count": 0,
            "cover_file": "cover.png",
            "visual_pages": [1],
            "ocr_pages": [],
            "ocr_required_pages": [],
            "warnings": [],
        },
        ensure_ascii=False,
    )
)
