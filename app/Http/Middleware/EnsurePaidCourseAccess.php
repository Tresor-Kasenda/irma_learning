<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\EnrollmentPaymentEnum;
use App\Models\Enrollment;
use App\Models\Formation;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsurePaidCourseAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $formation = $request->route('formation');

        if (! $formation instanceof Formation) {
            return $next($request);
        }

        if ((float) $formation->price <= 0) {
            return $next($request);
        }

        $enrollment = Enrollment::query()
            ->where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
            ->first();

        if (! $enrollment) {
            return redirect()->route('student.learnings.detail', $formation->slug)
                ->with('error', 'Vous devez payer pour accéder à cette formation.');
        }

        return $next($request);
    }
}
