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
                'contact_email' => $settings->contact_email ?: ApplicationSetting::DEFAULT_CONTACT_EMAIL,
                'contact_phone' => $settings->contact_phone ?: ApplicationSetting::DEFAULT_CONTACT_PHONE,
                'contact_address_primary' => $settings->contact_address_primary ?: ApplicationSetting::DEFAULT_CONTACT_ADDRESS_PRIMARY,
                'contact_address_secondary' => $settings->contact_address_secondary ?: ApplicationSetting::DEFAULT_CONTACT_ADDRESS_SECONDARY,
                'home_hero_title' => $settings->home_hero_title ?: ApplicationSetting::DEFAULT_HOME_HERO_TITLE,
                'home_hero_subtitle' => $settings->home_hero_subtitle ?: ApplicationSetting::DEFAULT_HOME_HERO_SUBTITLE,
                'home_features' => $settings->home_features ?: ApplicationSetting::DEFAULT_HOME_FEATURES,
                'auth_page_subtitle' => $settings->auth_page_subtitle ?: ApplicationSetting::DEFAULT_AUTH_PAGE_SUBTITLE,
                'auth_login_subtitle' => $settings->auth_login_subtitle ?: ApplicationSetting::DEFAULT_AUTH_LOGIN_SUBTITLE,
                'auth_register_subtitle' => $settings->auth_register_subtitle ?: ApplicationSetting::DEFAULT_AUTH_REGISTER_SUBTITLE,
            ],
        ];
    }
}
