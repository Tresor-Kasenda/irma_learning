<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\MobileMoneyCheckoutService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class ShwaryWebhookController extends Controller
{
    public function __invoke(Request $request, string $token, MobileMoneyCheckoutService $mobileMoney): Response
    {
        if (! $mobileMoney->hasValidWebhookToken($token)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        try {
            $mobileMoney->handleWebhook($request->getContent());
        } catch (Throwable $exception) {
            report($exception);

            abort(Response::HTTP_BAD_REQUEST);
        }

        return response()->noContent();
    }
}
