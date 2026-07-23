<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Enums\EnrollmentPaymentEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Services\StripeCheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Throwable;

final class PaymentController extends Controller
{
    public function __invoke(Request $request, Formation $formation): Response|RedirectResponse
    {
        if ($request->user()?->enrollments()
            ->where('formation_id', $formation->id)
            ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
            ->exists()) {
            return redirect()->route('course.player', $formation->id);
        }

        return Inertia::render('Student/Payment', [
            'formation' => [
                'id' => $formation->id,
                'slug' => $formation->slug,
                'title' => $formation->title,
                'short_description' => $formation->short_description,
                'image' => $formation->image,
                'price' => $formation->price,
                'duration_hours' => $formation->duration_hours,
                'sections_count' => $formation->sections()->count(),
            ],
        ]);
    }

    public function store(ProcessPaymentRequest $request, Formation $formation, StripeCheckoutService $stripeCheckout): RedirectResponse|SymfonyResponse
    {
        $user = $request->user();

        if ($user->enrollments()
            ->where('formation_id', $formation->id)
            ->whereIn('payment_status', [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE])
            ->exists()) {
            return redirect()->route('course.player', $formation->id)
                ->with('success', 'Vous êtes déjà inscrit à cette formation.');
        }

        try {
            $checkoutUrl = $stripeCheckout->startCheckout($user, $formation);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'payment_method' => 'Le paiement par carte est temporairement indisponible. Veuillez réessayer plus tard.',
            ]);
        }

        if ($checkoutUrl === null) {
            return redirect()->route('course.player', $formation->id)
                ->with('success', 'Paiement confirmé ! Bonne formation.');
        }

        return Inertia::location($checkoutUrl);
    }

    public function success(Request $request, Formation $formation, StripeCheckoutService $stripeCheckout): RedirectResponse
    {
        $enrollment = Enrollment::query()
            ->where('user_id', $request->user()?->id)
            ->where('formation_id', $formation->id)
            ->first();

        $checkoutSessionId = $request->string('session_id')->toString();

        if ($enrollment && $checkoutSessionId !== '') {
            try {
                $stripeCheckout->synchronizeCheckoutSession($enrollment, $checkoutSessionId);
                $enrollment->refresh();
            } catch (Throwable $exception) {
                report($exception);
            }
        }

        if (in_array($enrollment?->payment_status, [EnrollmentPaymentEnum::PAID, EnrollmentPaymentEnum::FREE], true)) {
            return redirect()->route('course.player', $formation->id)
                ->with('success', 'Paiement confirmé ! Bonne formation.');
        }

        return redirect()->route('student.payment.create', $formation->id)
            ->with('success', 'Votre paiement est en cours de confirmation. L\'accès sera ouvert dès validation par Stripe.');
    }
}
