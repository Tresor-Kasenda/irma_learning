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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
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

        $userKey = sprintf('access-code:user:%d:formation:%d', $user->id, $formation->id);
        $ipKey = sprintf('access-code:ip:%s:formation:%d', hash('sha256', (string) $request->ip()), $formation->id);

        if (RateLimiter::tooManyAttempts($userKey, 5) || RateLimiter::tooManyAttempts($ipKey, 20)) {
            $retryAfter = max(
                RateLimiter::availableIn($userKey),
                RateLimiter::availableIn($ipKey),
            );

            Log::warning('Access code brute force blocked', [
                'user_id' => $user->id,
                'email' => $user->email,
                'formation_id' => $formation->id,
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'code' => 'Trop de tentatives. Réessayez dans '.max(1, (int) ceil($retryAfter / 60)).' minute(s).',
            ]);
        }

        $accessGranted = DB::transaction(function () use ($formation, $request, $user): bool {
            $code = FormationAccessCode::query()
                ->whereBelongsTo($formation)
                ->where('code', $request->validated('code'))
                ->lockForUpdate()
                ->first();

            if (! $code || ! $code->isValid()) {
                return false;
            }

            $code->update([
                'is_used' => true,
                'user_id' => $user->id,
                'used_at' => now(),
            ]);

            $formation->enrollments()->firstOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => EnrollmentStatusEnum::ACTIVE,
                    'payment_status' => EnrollmentPaymentEnum::PAID,
                    'enrollment_date' => now(),
                    'progress_percentage' => 0,
                ],
            );

            return true;
        }, 3);

        if (! $accessGranted) {
            RateLimiter::hit($userKey, 1800);
            RateLimiter::hit($ipKey, 1800);

            Log::info('Access code validation failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'formation_id' => $formation->id,
                'attempt' => RateLimiter::attempts($userKey),
                'ip' => $request->ip(),
            ]);

            return back()->withErrors([
                'code' => 'Code d\'accès invalide ou déjà utilisé.',
            ]);
        }

        RateLimiter::clear($userKey);
        RateLimiter::clear($ipKey);

        return redirect()->route('formation.show', $formation)
            ->with('success', 'Votre code a été validé. Bonne formation !');
    }
}
