<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\MasterClassEnum;
use App\Models\MasterClass;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Administration')]
final class Dashboard extends Component
{
    public function render(): View
    {
        return view('dashboard');
    }

    #[Computed]
    public function masterClasses(): Collection
    {
        return MasterClass::query()
            ->where('status', '=', MasterClassEnum::PUBLISHED)
            ->withCount('chapters')
            ->withCount(['chapters as completed_chapters_count' => function ($query) {
                $query->whereHas('submission', function ($q) {
                    $q->where('user_id', auth()->id());
                });
            }])
            ->get()
            ->map(function ($masterClass) {
                $masterClass->progress = $masterClass->chapters_count > 0
                    ? round(($masterClass->completed_chapters_count / $masterClass->chapters_count) * 100)
                    : 0;

                return $masterClass;
            });
    }

    #[Computed]
    public function statistics(): array
    {
        $userId = auth()->id();

        return [
            'total' => MasterClass::query()
                ->where('status', '=', MasterClassEnum::PUBLISHED)
                ->whereHas('subscription', fn ($query) => $query->where('user_id', $userId))
                ->count(),
            'in_progress' => MasterClass::query()
                ->where('status', '=', MasterClassEnum::PUBLISHED)
                ->whereHas('subscription', fn ($query) => $query->where('user_id', $userId))
                ->whereHas('chapters.progress', fn (Builder $query) => $query
                    ->where('user_id', $userId)
                    ->where('status', 'in_progress')
                )
                ->count(),
            'completed' => MasterClass::query()
                ->where('status', '=', MasterClassEnum::PUBLISHED)
                ->whereHas('subscription', fn ($query) => $query->where('user_id', $userId))
                // Ensure all chapters are completed for the master class
                ->whereDoesntHave('chapters', function ($query) use ($userId) {
                    $query->whereDoesntHave('progress', fn ($q) => $q
                        ->where('user_id', $userId)
                        ->where('status', 'completed')
                    );
                })
                // Ensure the final exam for the final chapter is submitted
                ->whereHas('chapters', function ($query) use ($userId) {
                    $query->where('is_final_chapter', true)
                        ->whereHas('examination.submission', fn ($q) => $q->where('user_id', $userId));
                })
                ->count(),
        ];
    }
}
