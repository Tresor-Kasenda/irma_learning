<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAllChaptersCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param Closure(Request): (Response) $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $masterClass = $request->route('masterClass');

        $allChaptersCompleted = $masterClass->chapters()
            ->whereDoesntHave('progress', function ($query) {
                $query->where('user_id', auth()->id())
                    ->where('status', 'completed');
            })
            ->doesntExist();

        if (!$allChaptersCompleted) {
            return redirect()->route('student.course.learning', [
                'masterClass' => $masterClass
            ])->with('error', 'Vous devez compléter tous les chapitres avant de passer l\'examen final.');
        }

        return $next($request);
    }
}
