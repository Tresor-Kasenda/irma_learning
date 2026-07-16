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
            'contact_email' => 'contact@irma.test',
            'contact_phone' => '+243 000 000 000',
            'contact_address_primary' => 'Adresse principale',
            'contact_address_secondary' => 'Adresse secondaire',
            'home_hero_title' => 'Apprenez avec IRMA',
            'home_hero_subtitle' => 'Des parcours professionnels',
            'home_features' => ['Une première promesse.', 'Une deuxième promesse.', 'Une troisième promesse.'],
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
        ->and($settings->contact_email)->toBe('contact@irma.test')
        ->and($settings->home_features)->toHaveCount(3)
        ->and($settings->logo_path)->not->toBeNull();
    Storage::disk('public')->assertExists($settings->logo_path);
});

test('uploading a logo twice keeps a single settings row and the latest logo visible after refresh', function () {
    Storage::fake('public');
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

    $this->actingAs($admin)->post(route('admin.settings.update'), [
        'app_name' => 'IRMA Learning',
        'primary_color' => '#a23362',
        'default_currency' => 'USD',
        'allow_registration' => true,
        'logo' => UploadedFile::fake()->image('logo1.png'),
    ])->assertRedirect();

    $this->actingAs($admin)->post(route('admin.settings.update'), [
        'app_name' => 'IRMA Learning',
        'primary_color' => '#a23362',
        'default_currency' => 'USD',
        'allow_registration' => true,
        'logo' => UploadedFile::fake()->image('logo2.png'),
    ])->assertRedirect();

    expect(ApplicationSetting::query()->count())->toBe(1);

    $settings = ApplicationSetting::query()->firstOrFail();
    Storage::disk('public')->assertExists($settings->logo_path);

    $this->actingAs($admin)
        ->get(route('admin.settings.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('settings.logo_url', fn (string $url): bool => str_contains($url, $settings->logo_path)));
});

test('a student cannot access system settings', function () {
    $student = User::factory()->create(['role' => UserRoleEnum::STUDENT]);

    $this->actingAs($student)
        ->get(route('admin.settings.edit'))
        ->assertForbidden();
});
