<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MasterClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

final readonly class StudentMasterClassService
{
    public function __construct(
        private SubscriptionService $subscriptionService
    )
    {
    }

    /**
     * Obtenir les master classes auxquelles l'étudiant est souscrit
     */
    public function getSubscribedMasterClasses(User $student, ?string $search = null): Collection
    {
        $cacheKey = "student_{$student->id}_subscribed_classes_" . md5($search ?? '');

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($student, $search) {
            return MasterClass::query()
                ->whereHas('subscriptions', function ($query) use ($student) {
                    $query->where('user_id', $student->id);
                })
                ->with(['subscriptions' => function ($query) use ($student) {
                    $query->where('user_id', $student->id);
                }, 'chapters'])
                ->when($search, function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                })
                ->published()
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Obtenir les master classes disponibles (non souscrites) pour l'étudiant
     */
    public function getAvailableMasterClasses(User $student, ?string $search = null): Collection
    {
        $cacheKey = "student_{$student->id}_available_classes_" . md5($search ?? '');

        return Cache::remember($cacheKey, now()->addMinutes(15), function () use ($student, $search) {
            $subscribedIds = $student->subscriptions()->pluck('master_class_id');

            return MasterClass::query()
                ->whereNotIn('id', $subscribedIds)
                ->with(['chapters'])
                ->when($search, function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                })
                ->published()
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Obtenir les master classes payées par l'étudiant
     */
    public function getPaidMasterClasses(User $student, ?string $search = null): Collection
    {
        $cacheKey = "student_{$student->id}_paid_classes_" . md5($search ?? '');

        return Cache::remember($cacheKey, now()->addMinutes(30), function () use ($student, $search) {
            return MasterClass::query()
                ->whereHas('subscriptions', function ($query) use ($student) {
                    $query->where('user_id', $student->id)
                        ->where(function ($q) {
                            $q->where('status', 'completed')
                                ->orWhere('status', 'active');
                        });
                })
                ->whereNotNull('price')
                ->where('price', '>', 0)
                ->with(['subscriptions' => function ($query) use ($student) {
                    $query->where('user_id', $student->id);
                }, 'chapters'])
                ->when($search, function ($query) use ($search) {
                    $query->where('title', 'like', '%' . $search . '%')
                        ->orWhere('description', 'like', '%' . $search . '%');
                })
                ->published()
                ->orderBy('created_at', 'desc')
                ->get();
        });
    }

    /**
     * Vérifier si un étudiant peut accéder à une master class
     */
    public function canStudentAccessMasterClass(User $student, MasterClass $masterClass): array
    {
        $subscription = $student->subscriptions()
            ->where('master_class_id', $masterClass->id)
            ->first();

        $result = [
            'can_access' => false,
            'reason' => '',
            'subscription' => $subscription,
        ];

        // Si l'étudiant a une souscription
        if ($subscription) {
            $result['can_access'] = true;
            $result['reason'] = 'subscribed';
            return $result;
        }

        // Si la master class est gratuite
        if ($masterClass->isFree()) {
            $result['can_access'] = true;
            $result['reason'] = 'free';
            return $result;
        }

        // Sinon, l'étudiant doit payer
        $result['reason'] = 'payment_required';
        return $result;
    }

    /**
     * Obtenir les statistiques de l'étudiant
     */
    public function getStudentStats(User $student): array
    {
        $subscriptions = $student->subscriptions()->with('masterClass')->get();

        return [
            'total_subscriptions' => $subscriptions->count(),
            'active_subscriptions' => $subscriptions->where('status.value', 'active')->count(),
            'completed_subscriptions' => $subscriptions->where('status.value', 'completed')->count(),
            'total_paid_amount' => $subscriptions->sum(function ($subscription) {
                return $subscription->masterClass->price ?? 0;
            }),
            'average_progress' => $subscriptions->avg('progress') ?? 0,
        ];
    }

    /**
     * Nettoyer le cache pour un étudiant
     */
    public function clearStudentCache(User $student): void
    {
        $patterns = [
            "student_{$student->id}_subscribed_classes_*",
            "student_{$student->id}_available_classes_*",
            "student_{$student->id}_paid_classes_*",
        ];

        foreach ($patterns as $pattern) {
            Cache::forget($pattern);
        }
    }
}
