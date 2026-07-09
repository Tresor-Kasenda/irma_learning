<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\GenerateAccessCodesRequest;
use App\Models\Formation;
use App\Models\FormationAccessCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

final class AccessCodeController extends Controller
{
    private const array PER_PAGE_OPTIONS = [10, 25, 50, 100];

    public function index(Request $request): Response
    {
        $perPage = in_array($request->integer('per_page'), self::PER_PAGE_OPTIONS, true)
            ? $request->integer('per_page')
            : 25;

        $codes = FormationAccessCode::query()
            ->with(['formation:id,title', 'user:id,name,email'])
            ->when($request->string('search')->isNotEmpty(), fn (Builder $query): Builder => $query
                ->where('code', 'like', '%'.$request->string('search')->toString().'%'))
            ->when($request->filled('formation_id'), fn (Builder $query): Builder => $query
                ->where('formation_id', $request->integer('formation_id')))
            ->when($request->filled('is_used'), fn (Builder $query): Builder => $query
                ->where('is_used', $request->boolean('is_used')))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn (FormationAccessCode $code): array => [
                'id' => $code->id,
                'code' => $code->code,
                'formation' => $code->formation ? ['id' => $code->formation->id, 'title' => $code->formation->title] : null,
                'is_used' => $code->is_used,
                'user' => $code->user ? ['id' => $code->user->id, 'name' => $code->user->name] : null,
                'used_at' => $code->used_at?->toISOString(),
                'expires_at' => $code->expires_at?->toISOString(),
                'created_at' => $code->created_at?->toISOString(),
            ]);

        return Inertia::render('Admin/AccessCodes/Index', [
            'codes' => $codes,
            'filters' => $request->only('search', 'formation_id', 'is_used', 'per_page'),
            'formations' => Formation::query()->orderBy('title')->get(['id', 'title'])
                ->map(fn (Formation $formation): array => ['value' => (string) $formation->id, 'label' => $formation->title]),
        ]);
    }

    public function generate(GenerateAccessCodesRequest $request): RedirectResponse
    {
        $quantity = $request->validated('quantity');
        $formationId = $request->validated('formation_id');
        $expiresAt = $request->validated('expires_at');

        for ($i = 0; $i < $quantity; $i++) {
            FormationAccessCode::query()->create([
                'formation_id' => $formationId,
                'code' => Str::upper(Str::random(8)),
                'expires_at' => $expiresAt,
            ]);
        }

        return back()->with('success', "{$quantity} code(s) généré(s).");
    }

    public function destroy(FormationAccessCode $code): RedirectResponse
    {
        abort_if($code->is_used, 422, 'Ce code a déjà été utilisé et ne peut pas être supprimé.');

        $code->delete();

        return back()->with('success', 'Code supprimé.');
    }

    public function export(Request $request): HttpResponse
    {
        $codes = FormationAccessCode::query()
            ->with('formation:id,title')
            ->when($request->filled('formation_id'), fn (Builder $query): Builder => $query
                ->where('formation_id', $request->integer('formation_id')))
            ->when($request->filled('is_used'), fn (Builder $query): Builder => $query
                ->where('is_used', $request->boolean('is_used')))
            ->orderByDesc('created_at')
            ->get();

        $rows = ['code,formation,is_used,expires_at'];

        foreach ($codes as $code) {
            $rows[] = implode(',', [
                $code->code,
                '"'.str_replace('"', '""', $code->formation?->title ?? '').'"',
                $code->is_used ? '1' : '0',
                $code->expires_at?->toDateTimeString() ?? '',
            ]);
        }

        return response(implode("\n", $rows), 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="codes-acces.csv"',
        ]);
    }
}
