<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\UserProgressEnum;
use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Section;
use App\Models\UserProgress;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class UserProgressController extends Controller
{
    private const array PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function index(Request $request): Response
    {
        $perPage = in_array($request->integer('per_page'), self::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page')
            : 25;

        $progress = UserProgress::query()
            ->with('user:id,name,email')
            ->with(['trackable' => fn (MorphTo $morphTo) => $morphTo->morphWith([
                Chapter::class => ['section.formation'],
                Section::class => ['formation'],
            ])])
            ->when($request->string('search')->isNotEmpty(), function (Builder $query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->whereHas('user', fn (Builder $q) => $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->when($request->filled('status'), fn (Builder $query): Builder => $query
                ->where('status', $request->string('status')->toString()))
            ->when($request->filled('trackable_type'), fn (Builder $query): Builder => $query
                ->where('trackable_type', 'App\Models\\'.$request->string('trackable_type')->toString()))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (UserProgress $entry): array => [
                'id' => $entry->id,
                'user' => $entry->user ? ['id' => $entry->user->id, 'name' => $entry->user->name] : null,
                'trackable_type' => class_basename($entry->trackable_type),
                'trackable_title' => $entry->trackable?->title,
                'formation_title' => $this->formationTitle($entry),
                'status' => $entry->status->value,
                'status_label' => $entry->status->getLabel(),
                'time_spent' => $entry->time_spent,
                'started_at' => $entry->started_at?->toISOString(),
                'completed_at' => $entry->completed_at?->toISOString(),
            ]);

        return Inertia::render('Admin/Progress/Index', [
            'progress' => $progress,
            'filters' => $request->only('search', 'status', 'trackable_type', 'per_page'),
            'statusOptions' => collect(UserProgressEnum::cases())->map(fn (UserProgressEnum $status): array => ['value' => $status->value, 'label' => $status->getLabel()]),
        ]);
    }

    public function markStarted(UserProgress $progress): RedirectResponse
    {
        $progress->markAsStarted();

        return back()->with('success', 'Progression marquée comme commencée.');
    }

    public function markCompleted(UserProgress $progress): RedirectResponse
    {
        $progress->loadMissing('trackable');
        $progress->markAsCompleted();

        return back()->with('success', 'Progression marquée comme complétée.');
    }

    public function bulkMarkStarted(Request $request): RedirectResponse
    {
        UserProgress::query()->whereKey($request->input('ids', []))->get()->each->markAsStarted();

        return back()->with('success', 'Progressions marquées comme commencées.');
    }

    public function bulkMarkCompleted(Request $request): RedirectResponse
    {
        UserProgress::query()->whereKey($request->input('ids', []))->with('trackable')->get()->each->markAsCompleted();

        return back()->with('success', 'Progressions marquées comme complétées.');
    }

    private function formationTitle(UserProgress $entry): ?string
    {
        return match (true) {
            $entry->trackable instanceof Chapter => $entry->trackable->section?->formation?->title,
            $entry->trackable instanceof Section => $entry->trackable->formation?->title,
            default => null,
        };
    }
}
