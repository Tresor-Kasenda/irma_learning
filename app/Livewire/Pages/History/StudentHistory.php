<?php

declare(strict_types=1);

namespace App\Livewire\Pages\History;

use App\Models\ExamSubmission;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

class StudentHistory extends Component
{
    #[Url('q')]
    public ?string $search = null;

    public function render(): View
    {
        return view('livewire.pages.history.student-history');
    }

    #[Computed]
    public function submissions()
    {
        $student = Auth::user();
        $cacheKey = "student_history_{$student->id}_{$this->search}";

        return Cache::remember($cacheKey, now()->addMinutes(2), function () use ($student) {
            $submissions = ExamSubmission::query()
                ->with(['chapter.cours', 'examination'])
                ->whereBelongsTo($student);

            if (filled($this->search)) {
                $submissions->where(function ($query) {
                    $query->whereHas('chapter', function ($subQuery) {
                        $subQuery->whereAny([
                            'title',
                            'description',
                            'content',
                            'points'
                        ], 'like', '%' . $this->search . '%');
                    })
                        ->orWhereHas('examination', function ($subQuery) {
                            $subQuery->whereAny([
                                'title',
                                'description',
                                'duration',
                                'passing_score'
                            ], 'like', '%' . $this->search . '%');
                        });
                });
            }

            return $submissions->latest()->get();
        });
    }
}
