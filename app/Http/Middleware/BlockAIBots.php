<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class BlockAIBots
{
    private const array BLOCKED_USER_AGENTS = [
        'GPTBot',
        'ChatGPT-User',
        'OAI-SearchBot',
        'ClaudeBot',
        'Claude-Web',
        'anthropic-ai',
        'Google-Extended',
        'Google-Cloud-Samples',
        'PerplexityBot',
        'Perplexity-User',
        'Applebot-Extended',
        'Bytespider',
        'CCBot',
        'FacebookBot',
        'meta-externalagent',
        'cohere-ai',
        'Diffbot',
        'ImagesiftBot',
        'DataForSeoBot',
        'Meltwater',
        'AwarioBot',
        'omgili',
        'Seekr',
        'YouBot',
        'Amazonbot',
        'PetalBot',
        'SemrushBot',
        'AhrefsBot',
        'MJ12bot',
        'Screaming Frog',
        'Baiduspider',
        'YandexBot',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $userAgent = $request->userAgent();

        if ($userAgent === null || $userAgent === '') {
            return $next($request);
        }

        foreach (self::BLOCKED_USER_AGENTS as $bot) {
            if (str_contains($userAgent, $bot)) {
                return response()->noContent(Response::HTTP_FORBIDDEN);
            }
        }

        return $next($request);
    }
}
