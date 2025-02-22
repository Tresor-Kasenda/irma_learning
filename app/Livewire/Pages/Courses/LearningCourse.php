<?php

declare(strict_types=1);

namespace App\Livewire\Pages\Courses;

use App\Enums\SubscriptionEnum;
use App\Models\MasterClass;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('welcome')]
#[Title('Formations detailler')]
final class LearningCourse extends Component
{
    #[Locked]
    public MasterClass $masterClass;

    public function mount(MasterClass $masterClass): void
    {
        $this->masterClass = $masterClass->load('chapters');
    }

    public function render(): View
    {
        return view('livewire.pages.courses.learning-course');
    }

    public function subscribeToCourses(MasterClass $masterClass): void
    {
        if (!auth()->check()) {
            $this->redirect(route('login'), navigate: true);

            return;
        }

        if (!$masterClass->subscription()->where('user_id', Auth::id())->exists()) {
            $masterClass->subscription()->create([
                'user_id' => Auth::user()->id,
                'status' => SubscriptionEnum::ACTIVE,
                'progress' => 0,
                'started_at' => now(),
            ]);

            $this->dispatch(
                'notify',
                message: 'Vous êtes maintenant inscrit à cette formation !',
                type: 'success'
            );
        }

        $this->redirect(route('learning-course-student', $masterClass), navigate: true);
    }

    public function accessCourse(MasterClass $masterClass): void
    {
        $this->redirect(route('learning-course-student', $masterClass), navigate: true);
    }
}
