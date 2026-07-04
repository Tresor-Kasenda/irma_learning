<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class CertificateStudentController extends Controller
{
    public function index(Request $request): Response
    {
        $students = User::query()
            ->whereHas('certificates')
            ->withCount(['certificates', 'enrollments'])
            ->with(['certificates' => fn ($query) => $query->with('formation:id,title')->latest('issue_date')->limit(1)])
            ->when($request->string('search')->isNotEmpty(), function (Builder $query) use ($request): void {
                $search = $request->string('search')->toString();
                $query->where(fn (Builder $query): Builder => $query
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(in_array($request->integer('per_page'), [10, 25, 50, 100], true) ? $request->integer('per_page') : 10)
            ->withQueryString()
            ->through(fn (User $user): array => [
                ...$user->only(['id', 'name', 'email', 'avatar_url']),
                'certificates_count' => $user->certificates_count,
                'enrollments_count' => $user->enrollments_count,
                'latest_certificate' => $user->certificates->first() ? [
                    'formation' => $user->certificates->first()->formation?->title,
                    'issue_date' => $user->certificates->first()->issue_date?->toISOString(),
                ] : null,
            ]);

        return Inertia::render('Admin/Certificates/Index', [
            'students' => $students,
            'filters' => $request->only('search', 'per_page'),
        ]);
    }

    public function show(User $user): Response
    {
        abort_unless($user->certificates()->exists(), 404);

        $user->load([
            'certificates' => fn ($query) => $query->with('formation:id,title')->latest('issue_date'),
            'enrollments' => fn ($query) => $query->with('formation:id,title')->latest('enrollment_date'),
        ]);

        return Inertia::render('Admin/Certificates/Show', [
            'student' => [
                ...$user->only(['id', 'name', 'email', 'avatar_url']),
                'certificates' => $user->certificates->map(fn ($certificate): array => [
                    'id' => $certificate->id,
                    'formation' => $certificate->formation?->only(['id', 'title']),
                    'number' => $certificate->certificate_number,
                    'status' => $certificate->status->getLabel(),
                    'score' => (float) $certificate->final_score,
                    'issue_date' => $certificate->issue_date?->toISOString(),
                ]),
                'enrollments' => $user->enrollments->map(fn ($enrollment): array => [
                    'id' => $enrollment->id,
                    'formation' => $enrollment->formation?->only(['id', 'title']),
                    'status' => $enrollment->status->getLabel(),
                    'progress' => (float) $enrollment->progress_percentage,
                ]),
            ],
        ]);
    }
}
