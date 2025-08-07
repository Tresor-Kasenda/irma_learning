<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SubscriptionEnum;
use App\Models\MasterClass;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class SubscriptionService
{
    public function subscribeTo(User $user, MasterClass $masterClass): Subscription
    {
        return DB::transaction(function () use ($user, $masterClass) {
            // Check if already subscribed
            $existingSubscription = $this->getActiveSubscription($user, $masterClass);
            if ($existingSubscription) {
                return $existingSubscription;
            }

            // Create new subscription
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'master_class_id' => $masterClass->id,
                'status' => SubscriptionEnum::ACTIVE,
                'progress' => 0,
                'started_at' => now(),
            ]);

            // Clear cache
            $this->clearUserSubscriptionCache($user);

            return $subscription;
        });
    }

    public function getActiveSubscription(User $user, MasterClass $masterClass): ?Subscription
    {
        return $user->subscriptions()
            ->where('master_class_id', $masterClass->id)
            ->where('status', SubscriptionEnum::ACTIVE)
            ->first();
    }

    public function getUserSubscriptions(User $user): Collection
    {
        return Cache::remember(
            "user_subscriptions_{$user->id}",
            now()->addHours(1),
            fn() => $user->subscriptions()
                ->with(['masterClass.chapters'])
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function getAvailableMasterClasses(User $user): Collection
    {
        $subscribedIds = $user->subscriptions()->pluck('master_class_id');
        
        return Cache::remember(
            "available_master_classes_{$user->id}",
            now()->addMinutes(30),
            fn() => MasterClass::query()
                ->where('status', 'published')
                ->whereNotIn('id', $subscribedIds)
                ->with(['chapters', 'resources'])
                ->orderBy('created_at', 'desc')
                ->get()
        );
    }

    public function updateProgress(Subscription $subscription, float $progress): void
    {
        $subscription->update([
            'progress' => $progress,
            'completed_at' => $progress >= 100 ? now() : null,
            'status' => $progress >= 100 ? SubscriptionEnum::COMPLETED : SubscriptionEnum::ACTIVE,
        ]);

        $this->clearUserSubscriptionCache($subscription->user);
    }

    public function canAccessMasterClass(User $user, MasterClass $masterClass): bool
    {
        // Check if user is subscribed
        if ($this->getActiveSubscription($user, $masterClass)) {
            return true;
        }

        // Check if master class is free
        if ($masterClass->price === null || $masterClass->price <= 0) {
            return true;
        }

        return false;
    }

    private function clearUserSubscriptionCache(User $user): void
    {
        Cache::forget("user_subscriptions_{$user->id}");
        Cache::forget("available_master_classes_{$user->id}");
    }
}
