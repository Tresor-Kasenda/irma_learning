<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Services\StripeCheckoutService;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Exception\UnexpectedValueException;
use Symfony\Component\HttpFoundation\Response;

final class StripeWebhookController extends Controller
{
    public function __invoke(Request $request, StripeCheckoutService $stripeCheckout): Response
    {
        $signature = $request->header('Stripe-Signature');

        if (! is_string($signature) || $signature === '') {
            abort(Response::HTTP_BAD_REQUEST);
        }

        try {
            $stripeCheckout->handleWebhook($request->getContent(), $signature);
        } catch (SignatureVerificationException|UnexpectedValueException) {
            abort(Response::HTTP_BAD_REQUEST);
        }

        return response()->noContent();
    }
}
