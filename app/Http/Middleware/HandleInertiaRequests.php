<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApplicationSetting;
use Illuminate\Http\Request;
use Inertia\Middleware;

final class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $settings = ApplicationSetting::current();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'info' => fn () => $request->session()->get('info'),
            ],
            'appSettings' => [
                'name' => $settings->app_name,
                'tagline' => $settings->app_tagline,
                'logo_url' => $settings->logo_path ? '/storage/'.$settings->logo_path : '/images/irma-logo-base.svg',
                'primary_color' => $settings->primary_color,
                'default_currency' => $settings->default_currency,
                'allow_registration' => $settings->allow_registration,
                'support_email' => $settings->support_email,
            ],
        ];
    }
}
