<?php

namespace App\Service;

use App\Enums\VerificationCodeStatusEnum;
use App\Enums\VerificationCodeTypeEnum;
use App\Models\Formation;
use App\Models\Payment;
use App\Models\User;
use App\Models\VerificationCode;

class PaymentService
{
    public function processPaymentAndGenerateCode(User $user, Formation $formation, array $paymentData): array
    {
        $payment = Payment::create([
            'user_id' => $user->id,
            'formation_id' => $formation->id,
            'amount' => $formation->price,
            'status' => 'pending',
            'gateway_data' => $paymentData,
        ]);

        if ($this->processPayment($payment)) {
            $payment->markAsSuccess();

            $verificationCode = VerificationCode::create([
                'user_id' => $user->id,
                'formation_id' => $formation->id,
                'type' => VerificationCodeTypeEnum::Enrollment,
                'status' => VerificationCodeStatusEnum::Pending,
            ]);

            // Envoyer le code par email
            // Mail::to($user->email)->send(new VerificationCodeMail($verificationCode));

            return [
                'success' => true,
                'payment' => $payment,
                'verification_code' => $verificationCode,
            ];
        }

        return ['success' => false, 'payment' => $payment];
    }

    private function processPayment(Payment $payment): bool
    {
        return true;
    }
}
