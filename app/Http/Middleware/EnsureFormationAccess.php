<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Formation;
use Closure;
use Illuminate\Http\Request;

final class EnsureFormationAccess
{
    public function handle(Request $request, Closure $next)
    {
        $formation = $request->route('formation');

        if (! $formation instanceof Formation) {
            abort(404);
        }

        if (! auth()->user()->isEnrolledIn($formation)) {
            return redirect()->route('formations.show', $formation)
                ->with('error', 'Vous devez être inscrit à cette formation pour y accéder.');
        }

        return $next($request);
    }
}
