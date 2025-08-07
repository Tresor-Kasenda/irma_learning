<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\MasterClass;
use App\Services\SubscriptionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureMasterClassAccess
{
    public function __construct(
        private readonly SubscriptionService $subscriptionService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        $masterClass = $request->route('masterClass');
        
        if (!$masterClass instanceof MasterClass) {
            abort(404);
        }

        // Check if master class is published
        if (!$masterClass->isPublished()) {
            abort(404, 'Cette formation n\'est pas disponible.');
        }

        // Check if user can access this master class
        if (!$this->subscriptionService->canAccessMasterClass($user, $masterClass)) {
            return redirect()
                ->route('master-class', $masterClass)
                ->with('error', 'Vous devez être inscrit à cette formation pour y accéder.');
        }

        return $next($request);
    }
}
