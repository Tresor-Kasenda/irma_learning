<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;

trait LogsAllActivity
{
    use LogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName(Str::snake(class_basename($this)))
            ->logAll()
            ->logExcept($this->activityLogExcept())
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function tapActivity(Activity $activity, string $eventName): void
    {
        $request = request();

        $activity->properties = $activity->properties->merge([
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
        ]);
    }

    /**
     * Attributes that must never be written to the activity log (secrets, tokens, hashes).
     *
     * @return list<string>
     */
    protected function activityLogExcept(): array
    {
        return [];
    }
}
