<?php

namespace App\Livewire\Pages;

use App\Models\Chapter;
use App\Models\MasterClass;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout("layouts.app")]
#[Title("Apprendre")]
class StudentCourseLearning extends Component
{
    #[Locked]
    public MasterClass $masterClass;
    public ?Chapter $activeChapter = null;

    public function mount(MasterClass $masterClass): void
    {
        $this->masterClass = $masterClass->load(['resources', 'chapters', 'subscription']);
        $savedChapterId = session()->get("active_chapter_{$masterClass->id}");

        if ($savedChapterId) {
            $this->activeChapter = $this->masterClass->chapters->find($savedChapterId);
        }
    }

    public function render(): View
    {
        return view('livewire.pages.student-course-learning');
    }

    public function setActiveChapter($chapterId): void
    {
        $this->activeChapter = $this->masterClass->chapters->find($chapterId);
        session()->put("active_chapter_{$this->masterClass->id}", $chapterId);
    }
}
