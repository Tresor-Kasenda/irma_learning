<?php

declare(strict_types=1);

use App\Enums\ChapterTypeEnum;
use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserProgressEnum;
use App\Http\Controllers\Dashboard\Formations\StudentCertificationController;
use App\Http\Controllers\Dashboard\Formations\StudentFormationController;
use App\Http\Controllers\Dashboard\Formations\StudentLearningController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\Frontends\DetailFormationController;
use App\Http\Controllers\Frontends\FormationsController;
use App\Http\Controllers\Frontends\HomePageController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Pages\Courses\LearningCourse;
use App\Livewire\Pages\Frontend\Payments\StudentPayment;
use App\Livewire\ValidateFormationAccess;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\UserProgress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', HomePageController::class)->name('home-page');
Route::get('/formations', FormationsController::class)->name('certifications');
Route::get('/{formation:slug}/show', DetailFormationController::class)->name('formation.show');
Route::get('/nos-tarifs', [HomePageController::class, 'pricings'])->name('pages.pricings');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        $user = auth()->user();
        $catalogCountRelations = Formation::catalogCountRelations();

        $myEnrollments = Enrollment::query()
            ->with([
                'formation' => fn(BelongsTo $query): BelongsTo => $query
                    ->withCount($catalogCountRelations),
            ])
            ->where('user_id', $user->id)
            ->where('status', EnrollmentStatusEnum::ACTIVE->value)
            ->whereIn('payment_status', [
                EnrollmentPaymentEnum::PAID->value,
                EnrollmentPaymentEnum::FREE->value,
            ])
            ->latest('updated_at')
            ->get();

        $continueWatching = UserProgress::query()
            ->with(['trackable' => function ($query) use ($catalogCountRelations) {
                $query->with([
                    'section.formation' => fn(BelongsTo $query): BelongsTo => $query
                        ->withCount($catalogCountRelations),
                ]);
            }])
            ->where('user_id', $user->id)
            ->where('trackable_type', Chapter::class)
            ->where('status', UserProgressEnum::IN_PROGRESS->value)
            ->latest('updated_at')
            ->first();

        $search = request()->query('q');

        $recommendedFormations = Formation::query()
            ->withCount($catalogCountRelations)
            ->where('is_active', true)
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('formation_id')
                    ->from('enrollments')
                    ->where('user_id', $user->id);
            })
            ->when($search, function ($query) {
                $query->where(function ($q) {
                    $q->where('title', 'like', '%' . $search . '%')
                        ->orWhere('short_description', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                });
            })
            ->limit(8)
            ->latest()
            ->get();

        $stats = [
            'totalEnrollments' => Enrollment::where('user_id', $user->id)->count(),
            'activeEnrollments' => Enrollment::where('user_id', $user->id)
                ->where('status', EnrollmentStatusEnum::ACTIVE->value)
                ->whereIn('payment_status', [
                    EnrollmentPaymentEnum::PAID->value,
                    EnrollmentPaymentEnum::FREE->value,
                ])
                ->count(),
            'completedEnrollments' => Enrollment::where('user_id', $user->id)
                ->where('status', EnrollmentStatusEnum::COMPLETED->value)
                ->count(),
            'averageProgress' => (int)Enrollment::where('user_id', $user->id)
                ->whereIn('payment_status', [
                    EnrollmentPaymentEnum::PAID->value,
                    EnrollmentPaymentEnum::FREE->value,
                ])
                ->avg('progress_percentage'),
            'totalTimeSpent' => UserProgress::where('user_id', $user->id)->sum('time_spent'),
            'certificatesEarned' => DB::table('certificates')
                ->where('user_id', $user->id)
                ->where('status', 'active')
                ->count(),
        ];

        $popularCategories = [
            ['name' => 'Développement Web', 'count' => Formation::where('is_active', true)->count()],
            ['name' => 'Design', 'count' => 0],
            ['name' => 'Business', 'count' => 0],
            ['name' => 'Marketing', 'count' => 0],
        ];

        $activeChapters = Chapter::query()
            ->where('chapters.is_active', true)
            ->whereHas('section.formation', fn(Builder $query): Builder => $query->active());

        $catalogStats = [
            'formations' => Formation::query()->active()->count(),
            'videos' => (clone $activeChapters)->where('content_type', ChapterTypeEnum::VIDEO->value)->count(),
            'pdfs' => (clone $activeChapters)->where('content_type', ChapterTypeEnum::PDF->value)->count(),
            'texts' => (clone $activeChapters)->where('content_type', ChapterTypeEnum::TEXT->value)->count(),
        ];

        return Inertia::render('Dashboard', [
            'myEnrollments' => $myEnrollments,
            'continueWatching' => $continueWatching,
            'recommendedFormations' => $recommendedFormations,
            'stats' => $stats,
            'catalogStats' => $catalogStats,
            'popularCategories' => $popularCategories,
            'search' => $search,
        ]);
    })->name('dashboard');

    Route::get('/learnings', StudentFormationController::class)->name('student.learnings');
    Route::get('/certificats', StudentCertificationController::class)->name('certificats');
    Route::get('/inprogress', StudentLearningController::class)->name('student.progress');

    Route::post('/formation/{formation:id}/enroll', function (Formation $formation) {
        $user = auth()->user();

        if (!$user->hasStudent()) {
            return redirect()->back()->with('error', 'Seuls les étudiants peuvent s\'inscrire aux formations.');
        }

        if ($user->enrollments()->where('formation_id', $formation->id)->exists()) {
            return redirect()->route('course.player', $formation->id);
        }

        if ($formation->price > 0) {
            return redirect()->route('student.payment.create', $formation);
        }

        $user->enrollments()->create([
            'formation_id' => $formation->id,
            'enrollment_date' => now(),
            'status' => EnrollmentStatusEnum::ACTIVE->value,
            'payment_status' => EnrollmentPaymentEnum::FREE,
            'amount_paid' => 0,
            'progress_percentage' => 0,
        ]);

        return redirect()->route('course.player', $formation->id);
    })->name('formation.enroll');

    Route::get('/formation/{formation:id}/validate', ValidateFormationAccess::class)
        ->name('student.formations.validate-code');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/dash', [DashboardController::class, 'index'])->name('content');
    Route::get('/formation/{formation:id}/payment', StudentPayment::class)
        ->name('student.payment.create');

    Route::get('/course/{formation:id}/learn', function (Formation $formation) {
        $user = auth()->user();

        $formation->load(['sections.chapters' => function ($query) {
            $query->where('is_active', true)
                ->with(['exams' => fn($query) => $query->active()])
                ->orderBy('order_position');
        }]);

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
            ->where('status', EnrollmentStatusEnum::ACTIVE)
            ->first();

        if (!$enrollment) {
            return redirect()->route('formation.show', $formation->slug)
                ->with('error', 'Vous devez être inscrit à cette formation pour y accéder.');
        }

        $allChapters = $formation->sections
            ->flatMap(fn($section) => $section->chapters)
            ->values();

        $chapterId = request()->query('chapterId');
        $currentChapter = null;

        if ($chapterId) {
            $currentChapter = $allChapters->firstWhere('id', (int)$chapterId);
        } else {
            $lastProgress = UserProgress::where('user_id', $user->id)
                ->where('trackable_type', Chapter::class)
                ->whereIn('trackable_id', $allChapters->pluck('id'))
                ->where('status', UserProgressEnum::IN_PROGRESS)
                ->latest('updated_at')
                ->first();

            if ($lastProgress) {
                $currentChapter = $allChapters->firstWhere('id', $lastProgress->trackable_id);
            }
        }

        if (!$currentChapter) {
            $currentChapter = $allChapters->first();
        }

        $currentChapterPosition = $currentChapter
            ? $allChapters->search(fn($chapter) => $chapter->id === $currentChapter->id)
            : false;
        $currentChapterIndex = $currentChapterPosition === false ? 0 : $currentChapterPosition;

        if ($currentChapter && !UserProgress::where([
                'user_id' => $user->id,
                'trackable_type' => Chapter::class,
                'trackable_id' => $currentChapter->id,
            ])->exists()) {
            UserProgress::create([
                'user_id' => $user->id,
                'trackable_type' => Chapter::class,
                'trackable_id' => $currentChapter->id,
                'status' => UserProgressEnum::IN_PROGRESS,
                'started_at' => now(),
            ]);
        }

        $completedChapters = UserProgress::where('user_id', $user->id)
            ->where('trackable_type', Chapter::class)
            ->whereIn('trackable_id', $allChapters->pluck('id'))
            ->where('status', UserProgressEnum::COMPLETED)
            ->pluck('trackable_id')
            ->toArray();

        $chapterExam = $currentChapter?->exams()->active()->first();
        $hasPassedExam = true;
        if ($chapterExam) {
            $hasPassedExam = $chapterExam->hasUserPassed($user);
        }

        return Inertia::render('Courses/Player', [
            'formation' => $formation,
            'enrollment' => $enrollment,
            'allChapters' => $allChapters->values()->toArray(),
            'currentChapter' => $currentChapter,
            'currentChapterIndex' => $currentChapterIndex,
            'completedChapters' => $completedChapters,
            'htmlContent' => $currentChapter?->getHtmlContent() ?? '',
            'chapterExam' => $chapterExam,
            'hasPassedExam' => $hasPassedExam,
        ]);
    })->name('course.player');

    Route::post('/course/{formation:id}/chapter/{chapter}/complete', function (Formation $formation, Chapter $chapter) {
        $user = auth()->user();

        abort_unless(
            $chapter->section()->where('formation_id', $formation->id)->exists(),
            404,
        );

        $isEnrolled = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->where('status', EnrollmentStatusEnum::ACTIVE->value)
            ->whereIn('payment_status', [
                EnrollmentPaymentEnum::PAID->value,
                EnrollmentPaymentEnum::FREE->value,
            ])
            ->exists();

        abort_unless($isEnrolled, 403);

        $chapterExam = $chapter->exams()->active()->first();
        $hasPassedExam = true;
        if ($chapterExam) {
            $hasPassedExam = $chapterExam->hasUserPassed($user);
        }

        if ($chapterExam && !$hasPassedExam) {
            return redirect()->back()->with('error', 'Vous devez réussir l\'examen pour valider ce chapitre.');
        }

        $progress = UserProgress::firstOrNew([
            'user_id' => $user->id,
            'trackable_type' => Chapter::class,
            'trackable_id' => $chapter->id,
        ]);

        $progress->fill([
            'progress_percentage' => 100,
            'status' => UserProgressEnum::COMPLETED,
            'completed_at' => now(),
            'time_spent' => ($chapter->duration_minutes ?? 0) * 60,
        ])->save();

        $allChapters = $formation->sections()
            ->with(['chapters' => function ($query) {
                $query->where('is_active', true)->orderBy('order_position');
            }])
            ->get()
            ->flatMap(fn($section) => $section->chapters)
            ->values();

        $totalChapters = $allChapters->count();
        $completedCount = UserProgress::where('user_id', $user->id)
            ->where('trackable_type', Chapter::class)
            ->whereIn('trackable_id', $allChapters->pluck('id'))
            ->where('status', UserProgressEnum::COMPLETED)
            ->count();

        $progressPercentage = $totalChapters > 0 ? ($completedCount / $totalChapters) * 100 : 0;
        $enrollment = Enrollment::where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->first();

        if ($enrollment) {
            $enrollment->update([
                'progress_percentage' => round($progressPercentage, 2),
                'status' => $progressPercentage >= 100 ? EnrollmentStatusEnum::COMPLETED : EnrollmentStatusEnum::ACTIVE,
                'completion_date' => $progressPercentage >= 100 ? now() : null,
            ]);
        }

        $currentChapterIndex = $allChapters->search(fn($ch) => $ch->id === $chapter->id) ?: 0;

        if ($currentChapterIndex < $totalChapters - 1) {
            $nextChapter = $allChapters[$currentChapterIndex + 1];

            return redirect()->route('course.player', ['formation' => $formation->id, 'chapterId' => $nextChapter->id]);
        }

        return redirect()->route('course.player', $formation->id)
            ->with('success', 'Félicitations ! Vous avez terminé tous les chapitres !');
    })->name('course.chapter.complete');

    Route::get('/exam/{exam}/take', [ExamController::class, 'take'])
        ->name('exam.take');
    Route::post('/exam/{exam}/save-answer', [ExamController::class, 'saveAnswer'])
        ->name('exam.save-answer');
    Route::post('/exam/{exam}/submit', [ExamController::class, 'submit'])
        ->name('exam.submit');
    Route::get('/exam/attempt/{attempt}/results', [ExamController::class, 'results'])
        ->name('exam.results');

    Route::get('/master-class/{masterClass}/formations', LearningCourse::class)
        ->name('master-class')
        ->middleware('restrict.student.access');

    Route::group(['prefix' => 'enrollments'], function () {
        Route::get('/{enrollment}/invoice', EnrollmentController::class)
            ->name('enrollments.invoice');
        Route::post('/{enrollment}/refund', [EnrollmentController::class, 'refund'])
            ->name('enrollments.refund');
    });
});

require __DIR__ . '/auth.php';
