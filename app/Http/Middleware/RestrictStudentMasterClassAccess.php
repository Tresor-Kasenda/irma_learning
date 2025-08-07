<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\MasterClass;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class RestrictStudentMasterClassAccess
{
    /**
     * Handle an incoming request.
     * 
     * Restreint l'accès des étudiants authentifiés aux master classes:
     * - Ils ne peuvent voir que les master classes auxquelles ils sont souscrits
     * - Ils peuvent voir les autres mais doivent payer pour y accéder
     * - Ils peuvent voir les master classes qu'ils ont déjà payées
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user || !$user->isStudent()) {
            return $next($request);
        }

        $masterClass = $request->route('masterClass');
        
        if (!$masterClass instanceof MasterClass) {
            return $next($request);
        }

        // Vérifier si l'étudiant a accès à cette master class
        $hasSubscription = $user->subscriptions()
            ->where('master_class_id', $masterClass->id)
            ->exists();

        // Si l'étudiant n'est pas souscrit et que la master class n'est pas gratuite
        if (!$hasSubscription && !$masterClass->isFree()) {
            // Rediriger vers la page des formations de l'étudiant avec un message
            return redirect()
                ->route('student.my-master-classes', ['activeTab' => 'available'])
                ->with('error', 'Vous devez être inscrit ou acheter cette formation pour y accéder.');
        }

        return $next($request);
    }
}
