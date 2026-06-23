<?php

declare(strict_types=1);

namespace App\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

final class RecentExams extends Component
{
    public function render()
    {
        $attempts = Auth::user()
            ->examAttempts()
            ->with(['exam.examable'])
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.student.recent-exams', [
            'attempts' => $attempts,
        ]);
    }
}
