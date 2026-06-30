<?php

declare(strict_types=1);

namespace App\Http\Controllers\Frontends;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProcessPaymentRequest;
use App\Models\Formation;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

final class PaymentController extends Controller
{
    public function __invoke(Formation $formation): Response
    {
        return Inertia::render('Frontends/Payment', [
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

    public function store(ProcessPaymentRequest $request, Formation $formation): RedirectResponse
    {
        $user = $request->user();

        if ($user->enrollments()->where('formation_id', $formation->id)->exists()) {
            return redirect()->route('course.player', $formation->id)
                ->with('success', 'Vous êtes déjà inscrit à cette formation.');
        }

        $method = $request->validated('payment_method');
        $gateway = $method === 'card' ? 'stripe' : 'mobile_money';

        // SCAFFOLD: l'appel réel à la passerelle (Stripe Checkout / API Mobile Money)
        // se branchera ici. Le paiement est considéré comme réussi pour le moment.

        $user->enrollments()->create([
            'formation_id' => $formation->id,
            'enrollment_date' => now(),
            'status' => EnrollmentStatusEnum::ACTIVE,
            'payment_status' => EnrollmentPaymentEnum::PAID,
            'payment_method' => $method,
            'payment_gateway' => $gateway,
            'amount_paid' => $formation->price ?? 0,
            'currency' => 'USD',
            'payment_processed_at' => now(),
            'progress_percentage' => 0,
        ]);

        return redirect()->route('course.player', $formation->id)
            ->with('success', 'Paiement confirmé ! Bonne formation.');
    }
}
