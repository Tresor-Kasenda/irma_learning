<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

final class EnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCuratedEnrollments();
        $this->seedEnrollmentsForRemainingStudents();
    }

    /**
     * Scenarios for the demo students (ids 4-8). Guarded by user existence so the
     * seeder can also be run standalone (tests / re-seed) without violating the
     * user_id foreign key, and idempotent through updateOrCreate.
     */
    private function seedCuratedEnrollments(): void
    {
        $curated = [
            [
                'user_id' => 4,
                'formation_id' => 1,
                'status' => EnrollmentStatusEnum::COMPLETED->value,
                'payment_status' => EnrollmentPaymentEnum::PAID->value,
                'payment_method' => 'Mobile Money',
                'payment_transaction_id' => 'TXN-2024-001',
                'amount_paid' => 150000.00,
                'currency' => 'XAF',
                'progress_percentage' => 100.00,
                'enrollment_date' => now()->subMonths(3),
                'completion_date' => now()->subWeeks(2),
                'last_accessed_at' => now()->subWeeks(2),
            ],
            [
                'user_id' => 4,
                'formation_id' => 2,
                'status' => EnrollmentStatusEnum::ACTIVE->value,
                'payment_status' => EnrollmentPaymentEnum::PAID->value,
                'payment_method' => 'Carte bancaire',
                'payment_transaction_id' => 'TXN-2024-002',
                'amount_paid' => 120000.00,
                'currency' => 'XAF',
                'progress_percentage' => 60.00,
                'enrollment_date' => now()->subMonth(),
                'last_accessed_at' => now()->subDays(3),
            ],
            [
                'user_id' => 5,
                'formation_id' => 2,
                'status' => EnrollmentStatusEnum::ACTIVE->value,
                'payment_status' => EnrollmentPaymentEnum::PAID->value,
                'payment_method' => 'Orange Money',
                'payment_transaction_id' => 'TXN-2024-003',
                'amount_paid' => 120000.00,
                'currency' => 'XAF',
                'progress_percentage' => 30.00,
                'enrollment_date' => now()->subWeeks(2),
                'last_accessed_at' => now()->subDays(1),
            ],
            [
                'user_id' => 5,
                'formation_id' => 1,
                'status' => EnrollmentStatusEnum::ACTIVE->value,
                'payment_status' => EnrollmentPaymentEnum::FREE->value,
                'payment_method' => null,
                'amount_paid' => 0,
                'currency' => 'XAF',
                'progress_percentage' => 15.00,
                'enrollment_date' => now()->subDays(5),
                'last_accessed_at' => now()->subDays(2),
            ],
            [
                'user_id' => 6,
                'formation_id' => 3,
                'status' => EnrollmentStatusEnum::ACTIVE->value,
                'payment_status' => EnrollmentPaymentEnum::PAID->value,
                'payment_method' => 'MTN Mobile Money',
                'payment_transaction_id' => 'TXN-2024-004',
                'amount_paid' => 180000.00,
                'currency' => 'XAF',
                'progress_percentage' => 0.00,
                'enrollment_date' => now(),
                'last_accessed_at' => now(),
            ],
            [
                'user_id' => 7,
                'formation_id' => 1,
                'status' => EnrollmentStatusEnum::ACTIVE->value,
                'payment_status' => EnrollmentPaymentEnum::PAID->value,
                'payment_method' => 'Mobile Money',
                'payment_transaction_id' => 'TXN-2024-005',
                'amount_paid' => 150000.00,
                'currency' => 'XAF',
                'progress_percentage' => 80.00,
                'enrollment_date' => now()->subMonths(2),
                'last_accessed_at' => now()->subDays(1),
            ],
            [
                'user_id' => 8,
                'formation_id' => 1,
                'status' => EnrollmentStatusEnum::ACTIVE->value,
                'payment_status' => EnrollmentPaymentEnum::PAID->value,
                'payment_method' => 'Carte bancaire',
                'payment_transaction_id' => 'TXN-2024-006',
                'amount_paid' => 150000.00,
                'currency' => 'XAF',
                'progress_percentage' => 50.00,
                'enrollment_date' => now()->subMonth(),
                'last_accessed_at' => now()->subWeek(),
            ],
            [
                'user_id' => 8,
                'formation_id' => 3,
                'status' => EnrollmentStatusEnum::ACTIVE->value,
                'payment_status' => EnrollmentPaymentEnum::PENDING->value,
                'payment_method' => null,
                'payment_transaction_id' => 'TXN-2024-007',
                'amount_paid' => 0,
                'currency' => 'XAF',
                'progress_percentage' => 0.00,
                'enrollment_date' => now(),
                'last_accessed_at' => now(),
            ],
        ];

        $existingUserIds = User::query()
            ->whereIn('id', array_unique(array_column($curated, 'user_id')))
            ->pluck('id')
            ->all();

        $existingFormationIds = Formation::query()
            ->whereIn('id', array_unique(array_column($curated, 'formation_id')))
            ->pluck('id')
            ->all();

        foreach ($curated as $enrollment) {
            if (! in_array($enrollment['user_id'], $existingUserIds, true)) {
                continue;
            }

            if (! in_array($enrollment['formation_id'], $existingFormationIds, true)) {
                continue;
            }

            Enrollment::updateOrCreate(
                Arr::only($enrollment, ['user_id', 'formation_id']),
                Arr::except($enrollment, ['user_id', 'formation_id']),
            );
        }
    }

    /**
     * Give every student without any enrollment (e.g. a freshly registered account)
     * a baseline set so the dashboard and learnings pages are never empty for them.
     */
    private function seedEnrollmentsForRemainingStudents(): void
    {
        $activeFormations = Formation::query()
            ->active()
            ->orderBy('id')
            ->take(2)
            ->get();

        if ($activeFormations->isEmpty()) {
            return;
        }

        $students = User::query()
            ->where('role', UserRoleEnum::STUDENT->value)
            ->whereDoesntHave('enrollments')
            ->get();

        $primary = $activeFormations->first();
        $secondary = $activeFormations->get(1);

        foreach ($students as $student) {
            Enrollment::updateOrCreate(
                ['user_id' => $student->id, 'formation_id' => $primary->id],
                [
                    'status' => EnrollmentStatusEnum::ACTIVE->value,
                    'payment_status' => EnrollmentPaymentEnum::PAID->value,
                    'payment_method' => 'Mobile Money',
                    'amount_paid' => (float) ($primary->price ?? 0),
                    'currency' => 'XAF',
                    'progress_percentage' => 45.00,
                    'enrollment_date' => now()->subWeeks(2),
                    'last_accessed_at' => now()->subDays(2),
                ],
            );

            if ($secondary === null) {
                continue;
            }

            Enrollment::updateOrCreate(
                ['user_id' => $student->id, 'formation_id' => $secondary->id],
                [
                    'status' => EnrollmentStatusEnum::ACTIVE->value,
                    'payment_status' => EnrollmentPaymentEnum::FREE->value,
                    'payment_method' => null,
                    'amount_paid' => 0,
                    'currency' => 'XAF',
                    'progress_percentage' => 15.00,
                    'enrollment_date' => now()->subDays(5),
                    'last_accessed_at' => now()->subDays(1),
                ],
            );
        }
    }
}
