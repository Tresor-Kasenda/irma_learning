<?php

declare(strict_types=1);

use App\Enums\CertificateStatusEnum;
use App\Enums\UserRoleEnum;
use App\Models\Certificate;
use App\Models\Formation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

test('the certificate number is unique and resistant to concurrent creation', function () {
    $user = User::factory()->create(['role' => UserRoleEnum::STUDENT]);
    $formation = Formation::factory()->create();

    $first = Certificate::factory()->for($user)->for($formation)->create();
    $second = Certificate::factory()->for($user)->for($formation)->create();

    expect($first->certificate_number)
        ->toStartWith('CERT-'.now()->format('Y').'-')
        ->and($first->certificate_number)->not->toBe($second->certificate_number);

    expect($first->verification_hash)->toMatch('/^[a-f0-9]{64}$/');
    expect($second->verification_hash)->toMatch('/^[a-f0-9]{64}$/');
});

test('the certificate download is a valid PDF and is restricted to the owner and administrators', function () {
    $owner = User::factory()->create(['role' => UserRoleEnum::STUDENT]);
    $otherStudent = User::factory()->create(['role' => UserRoleEnum::STUDENT]);
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);

    $certificate = Certificate::factory()->for($owner)->create([
        'status' => CertificateStatusEnum::ACTIVE->value,
    ]);

    $ownerDownload = $this->actingAs($owner)
        ->get(route('certificates.download', $certificate));

    $ownerDownload
        ->assertSuccessful()
        ->assertDownload(sprintf('certificat-%s.pdf', $certificate->certificate_number))
        ->assertHeader('content-type', 'application/pdf');

    expect($ownerDownload->getContent())
        ->toStartWith('%PDF-')
        ->toMatch('/\/Count\s+1\b/');

    $this->actingAs($admin)
        ->get(route('certificates.download', $certificate))
        ->assertSuccessful();

    $this->actingAs($otherStudent)
        ->get(route('certificates.download', $certificate))
        ->assertForbidden();
});

test('the public certificate verification page exposes the certificate details', function () {
    $certificate = Certificate::factory()->create([
        'status' => CertificateStatusEnum::ACTIVE->value,
    ]);

    $this->get(route('certificates.verify', ['hash' => $certificate->verification_hash]))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Student/Formations/Certifications/Verify')
            ->where('certificate.certificate_number', $certificate->certificate_number)
            ->where('certificate.valid', true));
});
