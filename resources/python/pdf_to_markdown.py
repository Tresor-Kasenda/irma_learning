#!/usr/bin/env python3
"""Convert a PDF into editable Markdown while preserving visual references."""

from __future__ import annotations

import argparse
import collections
import json
import math
import re
import sys
from pathlib import Path
from typing import Any, Dict, List

try:
    import pymupdf
    import pymupdf4llm
except ImportError as exception:
    print(
        json.dumps(
            {
                "error": (
                    "Dépendances Python absentes. Exécutez: "
                    "python3 -m venv .venv-pdf && "
                    ".venv-pdf/bin/pip install -r resources/python/requirements.txt"
                ),
                "details": str(exception),
            },
            ensure_ascii=False,
        ),
        file=sys.stderr,
    )
    raise SystemExit(2)


def parse_arguments() -> argparse.Namespace:
    parser = argparse.ArgumentParser(description=__doc__)
    parser.add_argument("--input", required=True, type=Path)
    parser.add_argument("--output-dir", required=True, type=Path)
    parser.add_argument("--public-prefix", required=True)
    parser.add_argument("--max-pages", type=int, default=300)
    parser.add_argument("--image-dpi", type=int, default=144)
    parser.add_argument("--visual-dpi", type=int, default=110)
    parser.add_argument("--ocr-language", default="fra+eng")

    return parser.parse_args()


def public_url(prefix: str, filename: str) -> str:
    return f"{prefix.rstrip('/')}/{filename.lstrip('/')}"


def rewrite_image_paths(markdown: str, output_dir: Path, prefix: str) -> str:
    normalized_directory = output_dir.resolve().as_posix().rstrip("/")
    markdown = markdown.replace(normalized_directory + "/", prefix.rstrip("/") + "/")
    markdown = markdown.replace(str(output_dir).rstrip("/") + "/", prefix.rstrip("/") + "/")

    return markdown


def page_requires_visual_reference(page: pymupdf.Page, text: str) -> bool:
    if len(text.strip()) < 20:
        return True

    return bool(page.get_images(full=True) or page.get_drawings())


def render_page(page: pymupdf.Page, path: Path, dpi: int) -> None:
    scale = dpi / 72
    pixmap = page.get_pixmap(matrix=pymupdf.Matrix(scale, scale), alpha=False)
    pixmap.save(path)


def normalized_boundary_line(line: str) -> str:
    line = re.sub(r"^#{1,6}\s+", "", line.strip())
    line = re.sub(r"[*_`>#|]", "", line)
    line = re.sub(r"\d+", "#", line)
    line = re.sub(r"\s+", " ", line)

    return line.strip(" -–—·").casefold()


def is_page_number(line: str) -> bool:
    normalized = re.sub(r"[\u200b-\u200f\ufeff]", "", line).strip()
    normalized = re.sub(r"[*_`#>]", "", normalized).strip()

    return bool(
        re.fullmatch(
            r"(?:page\s*)?[-–—]?\s*\d+\s*(?:[/|]\s*\d+|(?:sur|of)\s+\d+)?\s*[-–—]?",
            normalized,
            flags=re.IGNORECASE,
        )
    )


def repeated_boundary_lines(markdown_pages: List[str]) -> set:
    if len(markdown_pages) < 2:
        return set()

    occurrences: collections.Counter = collections.Counter()
    for markdown in markdown_pages:
        lines = [line for line in markdown.splitlines() if line.strip()]
        candidates = {
            normalized_boundary_line(line)
            for line in [*lines[:3], *lines[-3:]]
            if normalized_boundary_line(line)
        }
        occurrences.update(candidates)

    threshold = max(2, math.ceil(len(markdown_pages) * 0.4))

    return {line for line, count in occurrences.items() if count >= threshold}


def clean_markdown_page(markdown: str, repeated_boundaries: set) -> str:
    cleaned_lines: List[str] = []

    for line in markdown.splitlines():
        if is_page_number(line):
            continue
        if normalized_boundary_line(line) in repeated_boundaries:
            continue

        cleaned_lines.append(line.rstrip())

    cleaned = "\n".join(cleaned_lines)
    cleaned = re.sub(r"!\[\s*\]\([^\n)]+\)", "", cleaned)
    cleaned = re.sub(r"[ \t]+\n", "\n", cleaned)
    cleaned = re.sub(r"\n{3,}", "\n\n", cleaned)

    return cleaned.strip()


def extract_document(arguments: argparse.Namespace) -> Dict[str, Any]:
    input_path = arguments.input.resolve()
    output_dir = arguments.output_dir.resolve()

    if not input_path.is_file():
        raise ValueError(f"Fichier PDF introuvable: {input_path}")

    output_dir.mkdir(parents=True, exist_ok=True)
    document = pymupdf.open(input_path)

    if document.needs_pass:
        raise ValueError("Le PDF est protégé par un mot de passe.")
    if document.page_count == 0:
        raise ValueError("Le PDF ne contient aucune page.")
    if document.page_count > arguments.max_pages:
        raise ValueError(
            f"Le PDF contient {document.page_count} pages; la limite est {arguments.max_pages}."
        )

    chunks = pymupdf4llm.to_markdown(
        document,
        page_chunks=True,
        write_images=True,
        image_path=str(output_dir),
        image_format="png",
        dpi=arguments.image_dpi,
        force_text=True,
        show_progress=False,
        margins=(0, 36, 0, 36),
    )

    raw_markdown_pages: List[str] = []
    scanned_pages: List[int] = []
    ocr_pages: List[int] = []
    visual_pages: List[int] = []
    warnings: List[str] = []

    cover_filename = "cover.png"
    render_page(document[0], output_dir / cover_filename, arguments.visual_dpi)

    for index, page in enumerate(document):
        page_number = index + 1
        extracted_text = page.get_text("text").strip()
        chunk_text = chunks[index].get("text", "") if index < len(chunks) else extracted_text
        chunk_text = rewrite_image_paths(chunk_text, output_dir, arguments.public_prefix).strip()

        is_scanned_page = len(extracted_text) < 20 and bool(page.get_images(full=True))
        if is_scanned_page:
            try:
                text_page = page.get_textpage_ocr(
                    language=arguments.ocr_language,
                    dpi=200,
                    full=True,
                )
                ocr_text = page.get_text("text", textpage=text_page).strip()
                if ocr_text:
                    chunk_text = (
                        f"{chunk_text}\n\n## Texte OCR — Page {page_number}\n\n{ocr_text}"
                    ).strip()
                    ocr_pages.append(page_number)
                    is_scanned_page = False
            except Exception as exception:
                warnings.append(
                    f"OCR indisponible pour la page {page_number}: {exception}"
                )

        raw_markdown_pages.append(chunk_text)

        if is_scanned_page:
            scanned_pages.append(page_number)

        if page_requires_visual_reference(page, extracted_text):
            visual_filename = f"page-{page_number:04d}.png"
            render_page(page, output_dir / visual_filename, arguments.visual_dpi)
            visual_pages.append(page_number)

    repeated_boundaries = repeated_boundary_lines(raw_markdown_pages)
    markdown_pages = [
        cleaned
        for cleaned in (
            clean_markdown_page(markdown_page, repeated_boundaries)
            for markdown_page in raw_markdown_pages
        )
        if cleaned
    ]
    markdown = "\n\n".join(markdown_pages).strip()

    if visual_pages:
        visual_reference = [
            "## Référence visuelle du document original",
            "",
            "> Ces pages conservent les schémas, formules, images et mises en page complexes pour vérification.",
            "",
        ]
        for page_number in visual_pages:
            visual_reference.extend(
                [
                    f"![Référence visuelle du document original]({public_url(arguments.public_prefix, f'page-{page_number:04d}.png')})",
                    "",
                ]
            )
        visual_markdown = "\n".join(visual_reference).strip()
        markdown = f"{markdown}\n\n{visual_markdown}".strip()

    if scanned_pages:
        warnings.append(
            "Certaines pages semblent scannées et nécessitent Tesseract OCR pour rendre leur texte éditable: "
            + ", ".join(str(page) for page in scanned_pages)
            + "."
        )

    image_files = sorted(
        path.name
        for path in output_dir.glob("*.png")
        if path.name != cover_filename and not path.name.startswith("page-")
    )
    word_count = len(re.findall(r"\b[\wÀ-ÿ'-]+\b", markdown, flags=re.UNICODE))

    return {
        "markdown": markdown,
        "page_count": document.page_count,
        "word_count": word_count,
        "image_count": len(image_files),
        "cover_file": cover_filename,
        "visual_pages": visual_pages,
        "ocr_pages": ocr_pages,
        "ocr_required_pages": scanned_pages,
        "warnings": warnings,
    }


def main() -> int:
    arguments = parse_arguments()

    try:
        result = extract_document(arguments)
    except Exception as exception:
        print(
            json.dumps({"error": str(exception)}, ensure_ascii=False),
            file=sys.stderr,
        )

        return 1

    print(json.dumps(result, ensure_ascii=False))

    return 0


if __name__ == "__main__":
    raise SystemExit(main())
