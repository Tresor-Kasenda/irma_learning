import importlib.util
import unittest
from pathlib import Path


SCRIPT_PATH = Path(__file__).parents[2] / "resources" / "python" / "pdf_to_markdown.py"
SPEC = importlib.util.spec_from_file_location("pdf_to_markdown", SCRIPT_PATH)
MODULE = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(MODULE)


class PdfMarkdownCleanupTest(unittest.TestCase):
    def test_removes_page_numbers_repeated_boundaries_and_empty_images(self):
        pages = [
            "Formation IRMA\n\n# Titre\n\nTexte page un.\n\n![](http://localhost/storage/image.png)\n\nPage 1 / 2\nConfidentiel",
            "Formation IRMA\n\n## Suite\n\nTexte page deux.\n\n- 2 -\nConfidentiel",
        ]

        boundaries = MODULE.repeated_boundary_lines(pages)
        cleaned = [MODULE.clean_markdown_page(page, boundaries) for page in pages]

        self.assertNotIn("Formation IRMA", cleaned[0])
        self.assertNotIn("Confidentiel", cleaned[1])
        self.assertNotIn("Page 1 / 2", cleaned[0])
        self.assertNotIn("![](", cleaned[0])
        self.assertIn("# Titre", cleaned[0])
        self.assertIn("Texte page deux.", cleaned[1])

    def test_collapses_excessive_blank_lines_without_merging_paragraphs(self):
        cleaned = MODULE.clean_markdown_page("Premier.\n\n\n\nSecond.", set())

        self.assertEqual("Premier.\n\nSecond.", cleaned)

    def test_detects_alternating_headers_and_footers(self):
        pages = [
            "IRMA — Paire\nTitre A\nContenu A\nPied pair",
            "IRMA — Impaire\nTitre B\nContenu B\nPied impair",
            "IRMA — Paire\nTitre C\nContenu C\nPied pair",
            "IRMA — Impaire\nTitre D\nContenu D\nPied impair",
        ]

        boundaries = MODULE.repeated_boundary_lines(pages)

        self.assertIn("irma — paire", boundaries)
        self.assertIn("irma — impaire", boundaries)
        self.assertIn("pied pair", boundaries)
        self.assertIn("pied impair", boundaries)

    def test_selects_an_ocr_first_strategy_for_scanned_documents(self):
        profile = MODULE.classify_document_profile([
            {"is_scanned": 1, "is_complex": 1, "image_count": 1},
            {"is_scanned": 1, "is_complex": 1, "image_count": 1},
            {"is_scanned": 1, "is_complex": 1, "image_count": 1},
            {"is_scanned": 1, "is_complex": 1, "image_count": 1},
            {"is_scanned": 0, "is_complex": 0, "image_count": 0},
        ])

        self.assertEqual("scanned", profile["document_type"])
        self.assertEqual("ocr-first", profile["strategy"])

    def test_selects_a_mixed_strategy_for_hybrid_documents(self):
        profile = MODULE.classify_document_profile([
            {"is_scanned": 0, "is_complex": 0, "image_count": 0},
            {"is_scanned": 1, "is_complex": 1, "image_count": 1},
            {"is_scanned": 0, "is_complex": 0, "image_count": 0},
        ])

        self.assertEqual("hybrid", profile["document_type"])
        self.assertEqual("mixed-text-ocr", profile["strategy"])

    def test_selects_a_text_first_strategy_for_native_documents(self):
        profile = MODULE.classify_document_profile([
            {"is_scanned": 0, "is_complex": 0, "image_count": 0},
            {"is_scanned": 0, "is_complex": 0, "image_count": 0},
        ])

        self.assertEqual("native", profile["document_type"])
        self.assertEqual("text-first", profile["strategy"])


if __name__ == "__main__":
    unittest.main()
