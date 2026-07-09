<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Enrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class EnrollmentController extends Controller
{
    public function index(Request $request): Response
    {
        $enrollments = Enrollment::query()
            ->with(['user:id,name,email,avatar', 'formation:id,title'])
            ->when($request->string('search')->isNotEmpty(), function (Builder $query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(function (Builder $query) use ($search): void {
                    $query->whereHas('user', fn (Builder $query): Builder => $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('formation', fn (Builder $query): Builder => $query->where('title', 'like', "%{$search}%"));
                });
            })
            ->when($request->filled('status'), fn (Builder $query): Builder => $query->where('status', $request->string('status')->toString()))
            ->when($request->filled('payment_status'), fn (Builder $query): Builder => $query->where('payment_status', $request->string('payment_status')->toString()))
            ->when($request->filled('student_id'), fn (Builder $query): Builder => $query->where('user_id', $request->integer('student_id')))
            ->latest('enrollment_date')
            ->paginate(in_array($request->integer('per_page'), [10, 25, 50, 100], true) ? $request->integer('per_page') : 10)
            ->withQueryString()
            ->through(fn (Enrollment $enrollment): array => [
                'id' => $enrollment->id,
                'user' => $enrollment->user?->only(['id', 'name', 'email', 'avatar_url']),
                'formation' => $enrollment->formation?->only(['id', 'title']),
                'status' => $enrollment->status->value,
                'status_label' => $enrollment->status->getLabel(),
                'payment_status' => $enrollment->payment_status->value,
                'payment_label' => $enrollment->payment_status->getLabel(),
                'progress_percentage' => (float) $enrollment->progress_percentage,
                'enrollment_date' => $enrollment->enrollment_date?->toISOString(),
            ]);

        return Inertia::render('Admin/Enrollments/Index', [
            'enrollments' => $enrollments,
            'filters' => $request->only('search', 'status', 'payment_status', 'student_id', 'per_page'),
            'students' => User::query()
                ->whereHas('enrollments')
                ->orderBy('name')
                ->get(['id', 'name', 'email'])
                ->map(fn (User $user): array => ['value' => (string) $user->id, 'label' => "{$user->name} · {$user->email}"]),
        ]);
    }
}
