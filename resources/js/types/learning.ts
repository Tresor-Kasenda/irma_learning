export interface LearningEnrollment {
    id: number;
    status?: string;
    payment_status?: string;
    progress_percentage: number | string | null;
    last_accessed_at?: string | null;
    updated_at?: string | null;
}

export interface LearningFormation {
    id: number;
    title: string;
    slug: string;
    short_description?: string | null;
    description?: string | null;
    image: string | null;
    difficulty_level: string;
    duration_hours: number | null;
    price: number | string | null;
    tags?: string[] | null;
    is_featured: boolean;
    chapter_count?: number;
    video_count?: number;
    pdf_count?: number;
    text_count?: number;
    students_count?: number;
    enrollments?: LearningEnrollment[];
}

export interface LearningCatalogStats {
    formations: number;
    videos: number;
    pdfs: number;
    texts: number;
}
