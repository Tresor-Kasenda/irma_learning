<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontends;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ValidateAccessCodeRequest;
use App\Models\Formation;
use App\Models\FormationAccessCode;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class FormationAccessController extends Controller
{
    public function create(Formation $formation): Response
    {
        return Inertia::render('Frontends/ValidateAccess', [
            'formation' => $formation->only(['id', 'slug', 'title', 'short_description', 'image']),
        ]);
    }

    public function store(ValidateAccessCodeRequest $request, Formation $formation): RedirectResponse
    {
        $user = $request->user();

        if ($formation->enrollments()->where('user_id', $user->id)->exists()) {
            return redirect()->route('formation.show', $formation)
                ->with('success', 'Vous avez déjà accès à cette formation.');
        }

        $code = FormationAccessCode::query()
            ->whereBelongsTo($formation)
            ->where('code', $request->validated('code'))
            ->where('is_used', false)
            ->first();

        if (! $code || ! $code->isValid()) {
            return back()->withErrors([
                'code' => 'Code d\'accès invalide ou déjà utilisé.',
            ]);
        }

        $code->update([
            'is_used' => true,
            'user_id' => $user->id,
            'used_at' => now(),
        ]);

        $formation->enrollments()->create([
            'user_id' => $user->id,
            'status' => EnrollmentStatusEnum::ACTIVE,
            'payment_status' => EnrollmentPaymentEnum::PAID,
            'enrollment_date' => now(),
            'progress_percentage' => 0,
        ]);

        return redirect()->route('formation.show', $formation)
            ->with('success', 'Votre code a été validé. Bonne formation !');
    }
}
