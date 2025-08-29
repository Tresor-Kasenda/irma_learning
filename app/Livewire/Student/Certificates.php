<?php

namespace App\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Certificates extends Component
{
    public function render()
    {
        $certificates = Auth::user()
            ->certificates()
            ->with('formation')
            ->latest()
            ->get();

        return view('livewire.student.certificates', [
            'certificates' => $certificates
        ]);
    }

    public function download($certificateId)
    {
        $certificate = Auth::user()
            ->certificates()
            ->findOrFail($certificateId);

        // Logique de téléchargement du certificat
        // À implémenter selon vos besoins
    }
}
