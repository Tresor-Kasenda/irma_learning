<?php

namespace App\Service;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\PaymentStatusEnum;
use App\Enums\VerificationCodeStatusEnum;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\Payment;
use App\Models\User;
use App\Models\VerificationCode;
use App\Notifications\EnrollmentVerificationCode;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class EnrollmentService
{
    public function initiateEnrollment(User $user, Formation $formation): array
    {
        try {
            DB::beginTransaction();

            if ($user->isEnrolledIn($formation)) {
                return [
                    'success' => false,
                    'message' => 'Vous êtes déjà inscrit à cette formation.',
                    'code' => 'ALREADY_ENROLLED'
                ];
            }

            $this->expirePreviousCodes($user, $formation);

            $verificationCode = VerificationCode::create([
                'user_id' => $user->id,
                'formation_id' => $formation->id,
                'type' => 'enrollment',
            ]);

            $user->notify(new EnrollmentVerificationCode($verificationCode, $formation));

            DB::commit();

            return [
                'success' => true,
                'message' => 'Un code de vérification a été envoyé à votre adresse email.',
                'verification_code_id' => $verificationCode->id
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de l\'initiation d\'inscription', [
                'user_id' => $user->id,
                'formation_id' => $formation->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez réessayer.',
                'code' => 'SYSTEM_ERROR'
            ];
        }
    }

    private function expirePreviousCodes(User $user, Formation $formation): void
    {
        VerificationCode::where('user_id', $user->id)
            ->where('formation_id', $formation->id)
            ->where('status', VerificationCodeStatusEnum::Pending)
            ->update(['status' => VerificationCodeStatusEnum::Expired]);
    }

    public function verifyCodeAndCreateEnrollment(User $user, Formation $formation, string $code): array
    {
        try {
            DB::beginTransaction();

            $verificationCode = VerificationCode::where('code', $code)
                ->where('user_id', $user->id)
                ->where('formation_id', $formation->id)
                ->active()
                ->first();

            if (!$verificationCode) {
                return [
                    'success' => false,
                    'message' => 'Code invalide, utilisé ou expiré.',
                    'code' => 'INVALID_CODE'
                ];
            }

            $verificationCode->markAsUsed(
                request()->ip(),
                request()->userAgent()
            );

            // Créer l'inscription
            $enrollment = Enrollment::create([
                'user_id' => $user->id,
                'formation_id' => $formation->id,
                'status' => EnrollmentStatusEnum::Suspended,
                'payment_status' => EnrollmentPaymentEnum::PENDING,
                'enrollment_date' => now(),
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Code vérifié avec succès. Procédez au paiement.',
                'enrollment' => $enrollment
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors de la vérification du code', [
                'user_id' => $user->id,
                'formation_id' => $formation->id,
                'code' => $code,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors de la vérification.',
                'code' => 'VERIFICATION_ERROR'
            ];
        }
    }

    public function processPayment(Enrollment $enrollment, array $paymentData): array
    {
        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'user_id' => $enrollment->user_id,
                'formation_id' => $enrollment->formation_id,
                'amount' => $enrollment->formation->price,
                'gateway' => $paymentData['gateway'],
                'gateway_transaction_id' => $paymentData['transaction_id'] ?? null,
                'status' => PaymentStatusEnum::PENDING,
            ]);

            $paymentResult = $this->processPaymentGateway($payment, $paymentData);

            if ($paymentResult['success']) {
                $payment->markAsSuccess();

                $enrollment->update([
                    'status' => EnrollmentStatusEnum::Active,
                    'payment_status' => EnrollmentPaymentEnum::PAID,
                    'amount_paid' => $payment->amount,
                ]);

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Paiement effectué avec succès. Vous pouvez maintenant accéder à la formation.',
                    'payment' => $payment
                ];
            } else {
                $payment->update(['status' => PaymentStatusEnum::FAILED]);
                DB::rollBack();

                return [
                    'success' => false,
                    'message' => 'Le paiement a échoué. Veuillez réessayer.',
                    'code' => 'PAYMENT_FAILED'
                ];
            }

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erreur lors du traitement du paiement', [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Une erreur est survenue lors du paiement.',
                'code' => 'PAYMENT_ERROR'
            ];
        }
    }

    private function processPaymentGateway(Payment $payment, array $data): array
    {
        return [
            'success' => true,
            'transaction_id' => 'demo_' . uniqid(),
            'message' => 'Paiement traité avec succès'
        ];
    }

    public function getEnrollmentProgress(Enrollment $enrollment): array
    {
        $formation = $enrollment->formation;

        $progressData = DB::table('user_progress as up')
            ->join('chapters as c', function ($join) {
                $join->on('up.trackable_id', '=', 'c.id')
                    ->where('up.trackable_type', '=', Chapter::class);
            })
            ->join('sections as s', 'c.section_id', '=', 's.id')
            ->join('modules as m', 's.module_id', '=', 'm.id')
            ->where('m.formation_id', $formation->id)
            ->where('up.user_id', $enrollment->user_id)
            ->select([
                'm.id as module_id',
                'm.title as module_title',
                's.id as section_id',
                's.title as section_title',
                'c.id as chapter_id',
                'c.title as chapter_title',
                'up.status',
                'up.progress_percentage',
                'up.started_at',
                'up.completed_at'
            ])
            ->get();

        $organizedProgress = [];
        foreach ($progressData as $progress) {
            $organizedProgress[$progress->module_id]['module'] = [
                'id' => $progress->module_id,
                'title' => $progress->module_title
            ];

            $organizedProgress[$progress->module_id]['sections'][$progress->section_id]['section'] = [
                'id' => $progress->section_id,
                'title' => $progress->section_title
            ];

            $organizedProgress[$progress->module_id]['sections'][$progress->section_id]['chapters'][] = [
                'id' => $progress->chapter_id,
                'title' => $progress->chapter_title,
                'status' => $progress->status,
                'progress_percentage' => $progress->progress_percentage,
                'started_at' => $progress->started_at,
                'completed_at' => $progress->completed_at,
            ];
        }

        return $organizedProgress;
    }
}
