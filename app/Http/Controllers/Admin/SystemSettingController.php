<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSystemSettingsRequest;
use App\Models\ApplicationSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

final class SystemSettingController extends Controller
{
    public function edit(): Response
    {
        $settings = ApplicationSetting::current();

        return Inertia::render('Admin/Settings/Edit', [
            'settings' => [
                ...$settings->only([
                    'app_name', 'app_tagline', 'support_email', 'logo_path', 'primary_color',
                    'default_currency', 'allow_registration', 'maintenance_message', 'certificate_signature_name',
                    'contact_email', 'contact_phone', 'contact_address_primary', 'contact_address_secondary',
                    'home_hero_title', 'home_hero_subtitle', 'home_features',
                ]),
                'contact_email' => $settings->contact_email ?: ApplicationSetting::DEFAULT_CONTACT_EMAIL,
                'contact_phone' => $settings->contact_phone ?: ApplicationSetting::DEFAULT_CONTACT_PHONE,
                'contact_address_primary' => $settings->contact_address_primary ?: ApplicationSetting::DEFAULT_CONTACT_ADDRESS_PRIMARY,
                'contact_address_secondary' => $settings->contact_address_secondary ?: ApplicationSetting::DEFAULT_CONTACT_ADDRESS_SECONDARY,
                'home_hero_title' => $settings->home_hero_title ?: ApplicationSetting::DEFAULT_HOME_HERO_TITLE,
                'home_hero_subtitle' => $settings->home_hero_subtitle ?: ApplicationSetting::DEFAULT_HOME_HERO_SUBTITLE,
                'home_features' => $settings->home_features ?: ApplicationSetting::DEFAULT_HOME_FEATURES,
                'logo_url' => $settings->logo_path ? '/storage/'.$settings->logo_path : '/images/irma-logo-base.svg',
            ],
        ]);
    }

    public function update(UpdateSystemSettingsRequest $request): RedirectResponse
    {
        $settings = ApplicationSetting::current();
        $data = $request->safe()->except('logo');

        if ($request->hasFile('logo')) {
            $previousLogo = $settings->logo_path;
            $data['logo_path'] = $request->file('logo')->store('branding', 'public');

            if ($previousLogo) {
                Storage::disk('public')->delete($previousLogo);
            }
        }

        $settings->update($data);

        return back()->with('success', 'Paramètres système mis à jour.');
    }
}
