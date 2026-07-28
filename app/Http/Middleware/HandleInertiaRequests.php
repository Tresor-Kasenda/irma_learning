<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\ApplicationSetting;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
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
            'notifications' => fn (): array => $this->notifications($request),
            'appSettings' => [
                'name' => $settings->app_name,
                'tagline' => $settings->app_tagline,
                'logo_url' => $settings->logoUrl(),
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
                'auth_login_title' => $settings->auth_login_title ?: ApplicationSetting::DEFAULT_AUTH_LOGIN_TITLE,
                'auth_register_title' => $settings->auth_register_title ?: ApplicationSetting::DEFAULT_AUTH_REGISTER_TITLE,
                'auth_login_subtitle' => $settings->auth_login_subtitle ?: ApplicationSetting::DEFAULT_AUTH_LOGIN_SUBTITLE,
                'auth_register_subtitle' => $settings->auth_register_subtitle ?: ApplicationSetting::DEFAULT_AUTH_REGISTER_SUBTITLE,
                'catalog_information_heading' => $settings->catalog_information_heading ?: ApplicationSetting::DEFAULT_CATALOG_INFORMATION_HEADING,
                'catalog_information_items' => $settings->catalog_information_items ?: ApplicationSetting::DEFAULT_CATALOG_INFORMATION_ITEMS,
            ],
        ];
    }

    /**
     * @return array{items: array<int, array{id: string, type: string, title: string, message: string, action_url: string|null, action_label: string|null, tone: string, read_at: string|null, created_at: string|null}>, unread_count: int}
     */
    private function notifications(Request $request): array
    {
        $user = $request->user();

        if ($user === null) {
            return [
                'items' => [],
                'unread_count' => 0,
            ];
        }

        return [
            'items' => $user->notifications()
                ->latest()
                ->limit(8)
                ->get()
                ->map(fn (DatabaseNotification $notification): array => [
                    'id' => (string) $notification->id,
                    'type' => $notification->type,
                    'title' => (string) ($notification->data['title'] ?? 'Notification'),
                    'message' => (string) ($notification->data['message'] ?? ''),
                    'action_url' => $notification->data['action_url'] ?? null,
                    'action_label' => $notification->data['action_label'] ?? null,
                    'tone' => (string) ($notification->data['tone'] ?? 'info'),
                    'read_at' => $notification->read_at?->toISOString(),
                    'created_at' => $notification->created_at?->toISOString(),
                ])
                ->all(),
            'unread_count' => $user->unreadNotifications()->count(),
        ];
    }
}
