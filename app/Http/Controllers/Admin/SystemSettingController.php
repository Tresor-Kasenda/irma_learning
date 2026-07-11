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
                ]),
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
