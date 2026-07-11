<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

final class ActivityLogController extends Controller
{
    private const array PER_PAGE_OPTIONS = [25, 50, 100, 200];

    public function index(Request $request): Response
    {
        $perPage = in_array($request->integer('per_page'), self::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page')
            : 50;

        $activities = Activity::query()
            ->with('causer')
            ->when($request->string('search')->isNotEmpty(), function (Builder $query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where('description', 'like', "%{$search}%");
            })
            ->when($request->filled('log_name'), fn (Builder $query): Builder => $query
                ->where('log_name', $request->string('log_name')->toString()))
            ->when($request->filled('event'), fn (Builder $query): Builder => $query
                ->where('event', $request->string('event')->toString()))
            ->when($request->filled('causer_id'), fn (Builder $query): Builder => $query
                ->where('causer_id', $request->integer('causer_id'))
                ->where('causer_type', User::class))
            ->when($request->filled('date_from'), fn (Builder $query): Builder => $query
                ->whereDate('created_at', '>=', $request->date('date_from')))
            ->when($request->filled('date_to'), fn (Builder $query): Builder => $query
                ->whereDate('created_at', '<=', $request->date('date_to')))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (Activity $activity): array => [
                'id' => $activity->id,
                'log_name' => $activity->log_name,
                'event' => $activity->event,
                'description' => $activity->description,
                'subject_type' => $activity->subject_type ? Str::afterLast($activity->subject_type, '\\') : null,
                'subject_id' => $activity->subject_id,
                'causer' => $activity->causer ? [
                    'id' => $activity->causer->id,
                    'name' => $activity->causer->name,
                ] : null,
                'properties' => $activity->properties,
                'created_at' => $activity->created_at?->toISOString(),
            ]);

        return Inertia::render('Admin/ActivityLogs/Index', [
            'activities' => $activities,
            'filters' => $request->only('search', 'log_name', 'event', 'causer_id', 'date_from', 'date_to', 'per_page'),
            'logNameOptions' => Activity::query()->distinct()->orderBy('log_name')->pluck('log_name')
                ->filter()
                ->map(fn (string $name): array => ['value' => $name, 'label' => $name]),
            'eventOptions' => Activity::query()->distinct()->orderBy('event')->pluck('event')
                ->filter()
                ->map(fn (string $event): array => ['value' => $event, 'label' => $event]),
        ]);
    }
}
