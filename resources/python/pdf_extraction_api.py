#!/usr/bin/env python3
"""
PDF Extraction API Server
Expose the PDF extraction algorithm via REST API
Deploy on VPS and call from Laravel via HTTP
"""

from fastapi import FastAPI, UploadFile, File, HTTPException, BackgroundTasks
from fastapi.responses import JSONResponse
from pathlib import Path
import json
import uuid
import logging
from typing import Optional
import asyncio
import tempfile
import shutil

from pdf_to_markdown import (
    parse_args,
    extract_document,
    validate_path,
)

app = FastAPI(
    title="PDF Extraction API",
    description="Extract markdown and cover from PDF documents",
    version="1.0.0",
)

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# In-memory job store (use Redis for production)
jobs = {}


@app.get("/health")
async def health_check():
    """Health check endpoint"""
    return {"status": "ok", "service": "PDF Extraction API"}


@app.post("/extract")
async def extract_pdf(
    file: UploadFile = File(...),
    max_pages: int = 0,
    batch_size: int = 50,
    parallel: int = 0,
    image_dpi: int = 144,
    visual_dpi: int = 110,
    ocr_language: str = "fra+eng",
):
    """
    Extract PDF content and generate cover image

    Args:
        file: PDF file to extract
        max_pages: Maximum pages to process (0 = all)
        batch_size: Pages per batch
        parallel: Number of parallel workers
        image_dpi: DPI for extracted images
        visual_dpi: DPI for cover image
        ocr_language: OCR language (e.g., "fra+eng", "eng")

    Returns:
        {
            "job_id": "uuid",
            "status": "processing" | "completed" | "failed",
            "markdown": "...",
            "cover_file": "cover.png",
            "page_count": 395,
            "word_count": 12345,
            "image_count": 0,
            "warnings": [],
            "error": null
        }
    """
    job_id = str(uuid.uuid4())

    try:
        # Create temporary directories
        with tempfile.TemporaryDirectory() as tmpdir:
            tmpdir = Path(tmpdir)
            input_file = tmpdir / file.filename
            output_dir = tmpdir / "output"
            output_dir.mkdir()

            # Save uploaded file
            content = await file.read()
            input_file.write_bytes(content)

            logger.info(f"Job {job_id}: Processing {file.filename}")

            # Prepare arguments
            args_list = [
                "--input", str(input_file),
                "--output-dir", str(output_dir),
                "--public-prefix", "/storage/extracted",
                "--max-pages", str(max_pages),
                "--batch-size", str(batch_size),
                "--parallel", str(parallel),
                "--image-dpi", str(image_dpi),
                "--visual-dpi", str(visual_dpi),
                "--ocr-language", ocr_language,
            ]

            # Parse arguments
            args = parse_args(args_list)

            # Extract document
            result = extract_document(args)

            # Read cover image
            cover_path = output_dir / result["cover_file"]
            cover_data = None
            if cover_path.exists():
                cover_data = cover_path.read_bytes()

            # Prepare response
            response = {
                "job_id": job_id,
                "status": "completed",
                "markdown": result["markdown"],
                "cover_file": result["cover_file"],
                "page_count": result["page_count"],
                "word_count": result["word_count"],
                "image_count": result["image_count"],
                "document_type": result["document_type"],
                "extraction_strategy": result["extraction_strategy"],
                "visual_pages": result["visual_pages"],
                "ocr_pages": result["ocr_pages"],
                "ocr_required_pages": result["ocr_required_pages"],
                "warnings": result["warnings"],
                "error": None,
            }

            jobs[job_id] = response
            logger.info(f"Job {job_id}: Completed successfully")

            return response

    except Exception as e:
        error_msg = f"{type(e).__name__}: {str(e)}"
        logger.error(f"Job {job_id}: {error_msg}")

        response = {
            "job_id": job_id,
            "status": "failed",
            "markdown": None,
            "cover_file": None,
            "page_count": 0,
            "word_count": 0,
            "image_count": 0,
            "document_type": None,
            "extraction_strategy": None,
            "visual_pages": [],
            "ocr_pages": [],
            "ocr_required_pages": [],
            "warnings": [],
            "error": error_msg,
        }

        jobs[job_id] = response
        return JSONResponse(status_code=500, content=response)


@app.get("/job/{job_id}")
async def get_job_status(job_id: str):
    """Get job status by ID"""
    if job_id not in jobs:
        raise HTTPException(status_code=404, detail="Job not found")

    return jobs[job_id]


if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8001)
