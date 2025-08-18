<?php

namespace App\Enums;

enum VerificationCodeTypeEnum: string
{
    case Enrollment = 'enrollment';
    case PasswordReset = 'password_reset';

    public function getLabel(): string
    {
        return match ($this) {
            self::Enrollment => 'Inscription',
            self::PasswordReset => 'Réinitialisation mot de passe',
        };
    }
}
