<?php

declare(strict_types=1);

use App\Enums\UserRoleEnum;
use App\Models\ApplicationSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('an administrator can view and update system settings', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

    $this->actingAs($admin)
        ->get(route('admin.settings.edit'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Settings/Edit')
            ->where('settings.app_name', 'IRMA Learning'));

    $this->actingAs($admin)
        ->post(route('admin.settings.update'), [
            'app_name' => 'IRMA Academy',
            'app_tagline' => 'Formations professionnelles',
            'support_email' => 'support@irma.test',
            'logo' => UploadedFile::fake()->image('logo.png', 320, 320),
            'primary_color' => '#8f2857',
            'default_currency' => 'USD',
            'allow_registration' => true,
            'maintenance_message' => null,
            'certificate_signature_name' => 'Direction IRMA',
        ])
        ->assertRedirect();

    $settings = ApplicationSetting::query()->firstOrFail();

    expect($settings->app_name)->toBe('IRMA Academy')
        ->and($settings->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($settings->logo_path);
});

test('a student cannot access system settings', function () {
    $student = User::factory()->create(['role' => UserRoleEnum::STUDENT]);

    $this->actingAs($student)
        ->get(route('admin.settings.edit'))
        ->assertForbidden();
});
