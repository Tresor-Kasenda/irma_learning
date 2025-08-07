<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\MasterClassEnum;
use App\Enums\SubscriptionEnum;
use App\Models\MasterClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class DashboardService
{
    public function getUserSubscribedMasterClasses(User $user, ?string $search = null): Collection
    {
        $cacheKey = "user_subscribed_master_classes_{$user->id}_{$search}";

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($user, $search) {
            return MasterClass::query()
                ->where('status', MasterClassEnum::PUBLISHED)
                ->whereExists(function ($query) use ($user) {
                    $query->select(DB::raw(1))
                        ->from('subscriptions')
                        ->whereColumn('subscriptions.master_class_id', 'master_classes.id')
                        ->where('subscriptions.user_id', $user->id)
                        ->where('subscriptions.status', SubscriptionEnum::ACTIVE->value);
                })
                ->when($search, function ($query) use ($search) {
                    $query->where(function ($query) use ($search) {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('duration', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%")
                            ->orWhere('sub_title', 'like', "%{$search}%");
                    });
                })
                ->withCount('chapters')
                ->withCount(['chapters as completed_chapters_count' => function ($query) use ($user) {
                    $query->whereHas('progress', function ($q) use ($user) {
                        $q->where('user_id', $user->id)
                            ->where('status', 'completed');
                    });
                }])
                ->get()
                ->map(function ($masterClass) {
                    $masterClass->progress = $masterClass->chapters_count > 0
                        ? round(($masterClass->completed_chapters_count / $masterClass->chapters_count) * 100)
                        : 0;
                    return $masterClass;
                });
        });
    }

    public function getUserStatistics(User $user): array
    {
        $cacheKey = "user_statistics_{$user->id}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user) {
            // Query optimisée avec raw SQL pour éviter les N+1
            $stats = DB::select('
                SELECT 
                    COUNT(DISTINCT mc.id) as total,
                    COUNT(DISTINCT CASE 
                        WHEN cp.status = "in_progress" THEN mc.id 
                    END) as in_progress,
                    COUNT(DISTINCT CASE 
                        WHEN es.id IS NOT NULL AND c.is_final_chapter = 1 THEN mc.id 
                    END) as completed
                FROM master_classes mc
                INNER JOIN subscriptions s ON s.master_class_id = mc.id
                LEFT JOIN chapters c ON c.master_class_id = mc.id
                LEFT JOIN chapter_progress cp ON cp.chapter_id = c.id AND cp.user_id = ?
                LEFT JOIN examinations ex ON ex.chapter_id = c.id AND c.is_final_chapter = 1
                LEFT JOIN exam_submissions es ON es.examination_id = ex.id AND es.user_id = ?
                WHERE mc.status = ?
                AND s.user_id = ?
                AND s.status = ?
            ', [$user->id, $user->id, MasterClassEnum::PUBLISHED->value, $user->id, SubscriptionEnum::ACTIVE->value]);

            return [
                'total' => (int) $stats[0]->total,
                'in_progress' => (int) $stats[0]->in_progress,
                'completed' => (int) $stats[0]->completed,
            ];
        });
    }

    public function clearUserCache(User $user): void
    {
        $patterns = [
            "user_statistics_{$user->id}",
            "user_subscribed_master_classes_{$user->id}_*",
        ];

        foreach ($patterns as $pattern) {
            if (str_contains($pattern, '*')) {
                // Pour les patterns avec wildcard, on devrait utiliser Redis ou un cache tags
                // Pour l'instant, on clear le cache simple
                Cache::forget(str_replace('_*', '_', $pattern));
            } else {
                Cache::forget($pattern);
            }
        }
    }
}
