<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontends;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\UserProgressEnum;
use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Formation;
use App\Models\UserProgress;
use Inertia\Inertia;

final class DetailFormationController extends Controller
{
    public function __invoke(Formation $formation)
    {
        $formation->load([
            'sections' => fn ($q) => $q->orderBy('order_position')->with([
                'chapters' => fn ($q) => $q->where('is_active', true)->orderBy('order_position'),
            ]),
        ]);

        $chapterCount = $formation->sections->flatMap->chapters->count();
        $isEnrolled = false;
        $completedChapterIds = [];

        if (auth()->check() && auth()->user()->hasStudent()) {
            $enrollment = auth()->user()->enrollments()
                ->whereBelongsTo($formation)
                ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
                ->first();

            $isEnrolled = $enrollment !== null;

            if ($isEnrolled) {
                $allChapterIds = $formation->sections->flatMap->chapters->pluck('id');
                $completedChapterIds = UserProgress::query()
                    ->where('user_id', auth()->id())
                    ->where('trackable_type', Chapter::class)
                    ->whereIn('trackable_id', $allChapterIds)
                    ->where('status', UserProgressEnum::COMPLETED)
                    ->pluck('trackable_id')
                    ->toArray();
            }
        }

        return Inertia::render('Frontends/ShowFormation', [
            'formation' => $formation,
            'chapterCount' => $chapterCount,
            'isEnrolled' => $isEnrolled,
            'completedChapterIds' => $completedChapterIds,
        ]);
    }
}
