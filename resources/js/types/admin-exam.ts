export interface ExamOptionForm {
    id?: number | null;
    option_text: string;
    is_correct: boolean;
    order_position: number;
}

export interface ExamQuestionForm {
    id?: number | null;
    question_text: string;
    question_type: string;
    points: number;
    is_required: boolean;
    explanation: string;
    options: ExamOptionForm[];
}

export interface ExamEditorData {
    id?: number;
    title: string;
    description: string | null;
    instructions: string | null;
    duration_minutes: number;
    passing_score: number;
    max_attempts: number;
    randomize_questions: boolean;
    show_results_immediately: boolean;
    is_active: boolean;
    questions: ExamQuestionForm[];
}

export function createEmptyExam(): ExamEditorData {
    return {
        title: '',
        description: '',
        instructions: '',
        duration_minutes: 60,
        passing_score: 70,
        max_attempts: 3,
        randomize_questions: false,
        show_results_immediately: true,
        is_active: true,
        questions: [],
    };
}
