<?php

declare(strict_types=1);

use App\Enums\CertificateStatusEnum;
use App\Models\Certificate;
use App\Models\Formation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('guests are redirected to login', function () {
    $this->get(route('certificats'))->assertRedirect(route('login'));
});

test('it lists the active certificates of the authenticated learner', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['title' => 'Cybersécurité']);
    $certificate = Certificate::factory()->for($user)->for($formation)->create([
        'status' => CertificateStatusEnum::ACTIVE,
        'final_score' => 88,
    ]);

    // Another learner's certificate must not leak.
    Certificate::factory()->create(['status' => CertificateStatusEnum::ACTIVE]);

    $this->actingAs($user)
        ->get(route('certificats'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Formations/Certifications/Index')
            ->has('certificates', 1)
            ->where('certificates.0.id', $certificate->id)
            ->where('certificates.0.formation.title', 'Cybersécurité')
            ->where('certificates.0.final_score', '88.00')
            ->etc());
});

test('it excludes non-active certificates', function () {
    $user = User::factory()->create();
    Certificate::factory()->for($user)->create(['status' => CertificateStatusEnum::REVOKED]);

    $this->actingAs($user)
        ->get(route('certificats'))
        ->assertInertia(fn (Assert $page) => $page->has('certificates', 0)->etc());
});

test('the certificate page is restricted to its owner', function () {
    $owner = User::factory()->create();
    $certificate = Certificate::factory()->for($owner)->create();

    $this->actingAs(User::factory()->create())
        ->get(route('certificats.show', $certificate))
        ->assertForbidden();

    $this->actingAs($owner)
        ->get(route('certificats.show', $certificate))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Formations/Certifications/Show')
            ->where('certificate.id', $certificate->id)
            ->etc());
});
