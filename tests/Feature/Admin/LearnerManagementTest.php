<?php

declare(strict_types=1);

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\Formation;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->student = User::factory()->create(['role' => 'student', 'name' => 'Aline Test']);
    $this->formation = Formation::factory()->create(['title' => 'Formation Marketing']);
});

test('an admin can list formation enrollments', function () {
    Enrollment::factory()->for($this->student)->for($this->formation)->create(['progress_percentage' => 42]);

    $this->actingAs($this->admin)
        ->get(route('admin.enrollments.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Enrollments/Index')
            ->has('enrollments.data', 1)
            ->where('enrollments.data.0.user.name', 'Aline Test')
            ->where('enrollments.data.0.formation.title', 'Formation Marketing')
            ->where('enrollments.data.0.progress_percentage', 42)
            ->etc());
});

test('the certificate list only contains students with a certificate', function () {
    $withoutCertificate = User::factory()->create(['role' => 'student']);
    Certificate::factory()->for($this->student)->for($this->formation)->create();

    $this->actingAs($this->admin)
        ->get(route('admin.certificates.index'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Certificates/Index')
            ->has('students.data', 1)
            ->where('students.data.0.id', $this->student->id)
            ->where('students.data.0.certificates_count', 1)
            ->missing('students.data.1')
            ->etc());

    expect($withoutCertificate->certificates()->exists())->toBeFalse();
});

test('an admin can view a certified student profile with certificates and progress', function () {
    Certificate::factory()->for($this->student)->for($this->formation)->create(['final_score' => 88]);
    Enrollment::factory()->for($this->student)->for($this->formation)->create(['progress_percentage' => 100]);

    $this->actingAs($this->admin)
        ->get(route('admin.certificates.show', $this->student))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Admin/Certificates/Show')
            ->where('student.name', 'Aline Test')
            ->where('student.certificates.0.score', 88)
            ->where('student.enrollments.0.progress', 100)
            ->etc());
});

test('a student cannot access learner administration', function () {
    $this->actingAs($this->student)
        ->get(route('admin.enrollments.index'))
        ->assertForbidden();
});
