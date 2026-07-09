<?php

declare(strict_types=1);

use App\Enums\UserRoleEnum;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;

test('a formation cannot be activated while a section assessment is missing', function () {
    $admin = User::factory()->create(['role' => UserRoleEnum::ADMIN]);
    $formation = Formation::factory()->create(['is_active' => false]);
    Section::factory()->for($formation)->create(['is_active' => true]);

    $this->actingAs($admin)
        ->patch(route('admin.formations.toggle-active', $formation->id))
        ->assertRedirect()
        ->assertSessionHas('error');

    expect($formation->refresh()->is_active)->toBeFalse();
});
