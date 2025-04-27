<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\MasterClassEnum;
use App\Models\MasterClass;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Administration')]
final class Dashboard extends Component
{
    #[Url('q')]
    #[Validate('nullable|string')]
    public ?string $search = null;

    public function render(): View
    {
        return view('dashboard');
    }

    #[Computed]
    public function statistics(): array
    {
        $userId = auth()->id();
        $cacheKey = "statistics_{$userId}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($userId) {
            $baseQuery = MasterClass::query()
                ->where('status', '=', MasterClassEnum::PUBLISHED)
                ->whereHas('subscription', fn($query) => $query->where('user_id', $userId));

            return [
                'total' => (clone $baseQuery)->count(),
                'in_progress' => (clone $baseQuery)
                    ->whereHas('chapters.progress', fn(Builder $query) => $query
                        ->where('user_id', $userId)
                        ->where('status', 'in_progress')
                    )
                    ->count(),
                'completed' => (clone $baseQuery)
                    ->whereExists(function ($query) use ($userId) {
                        $query->selectRaw('1')
                            ->from('chapters')
                            ->whereColumn('chapters.master_class_id', 'master_classes.id')
                            ->where('is_final_chapter', true)
                            ->whereExists(function ($subquery) use ($userId) {
                                $subquery->selectRaw('1')
                                    ->from('exam_submissions')
                                    ->join('examinations', 'examinations.id', '=', 'exam_submissions.examination_id')
                                    ->whereColumn('examinations.chapter_id', 'chapters.id')
                                    ->where('exam_submissions.user_id', $userId);
                            });
                    })
                    ->whereNotExists(function ($query) use ($userId) {
                        $query->selectRaw('1')
                            ->from('chapters')
                            ->whereColumn('chapters.master_class_id', 'master_classes.id')
                            ->whereNotExists(function ($subquery) use ($userId) {
                                $subquery->selectRaw('1')
                                    ->from('chapter_progress')
                                    ->whereColumn('chapter_progress.chapter_id', 'chapters.id')
                                    ->where('chapter_progress.user_id', $userId)
                                    ->where('chapter_progress.status', 'completed');
                            });
                    })
                    ->count(),
            ];
        });
    }

    #[Computed]
    public function masterClasses(): Collection
    {
        $userId = auth()->id();
        $cacheKey = "master_classes_{$userId}_{$this->search}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($userId) {
            return MasterClass::query()
                ->where('status', '=', MasterClassEnum::PUBLISHED)
                ->when($this->search, function (Builder $query) {
                    $query->where(function (Builder $query) {
                        $query->where('title', 'like', "%{$this->search}%")
                            ->orWhere('duration', 'like', "%{$this->search}%")
                            ->orWhere('description', 'like', "%{$this->search}%")
                            ->orWhere('sub_title', 'like', "%{$this->search}%");
                    });
                })
                ->withCount('chapters')
                ->withCount(['chapters as completed_chapters_count' => function (Builder $query) use ($userId) {
                    $query->whereHas('progress', function (Builder $q) use ($userId) {
                        $q->where('user_id', $userId)
                            ->where('status', 'completed');
                    });
                }])
                ->get()
                ->map(function ($masterClass) {
                    $masterClass->progress = $masterClass->chapters_count > 0
                        ? round(($masterClass->completed_chapters_count / $masterClass->chapters_count) * 100)
                        : 0;

                    return $masterClass;
                });
        });
    }
}
