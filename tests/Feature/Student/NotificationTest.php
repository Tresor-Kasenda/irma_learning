<?php

declare(strict_types=1);

use App\Models\User;
use App\Notifications\LearningActivityNotification;
use Inertia\Testing\AssertableInertia as Assert;

test('registration stores a welcome notification', function () {
    $this->post('/register', [
        'name' => 'Marie Apprenante',
        'email' => 'marie@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard', absolute: false));

    $user = User::query()->where('email', 'marie@example.com')->firstOrFail();

    expect($user->notifications()
        ->where('type', 'learning.welcome')
        ->count())->toBe(1);
});

test('unread notifications are shared with the learning layout', function () {
    $user = User::factory()->create();
    $user->notify(new LearningActivityNotification(
        notificationType: 'learning.welcome',
        title: 'Bienvenue sur IRMA Learning',
        message: 'Votre espace apprenant est prêt.',
        actionUrl: route('dashboard'),
        actionLabel: 'Découvrir mon espace',
    ));

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful()
        ->assertInertia(fn (Assert $page) => $page
            ->where('notifications.unread_count', 1)
            ->has('notifications.items', 1)
            ->where('notifications.items.0.type', 'learning.welcome')
            ->where('notifications.items.0.title', 'Bienvenue sur IRMA Learning')
            ->etc());
});

test('the first dashboard access welcomes an existing learner only once', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertSuccessful();

    expect($user->notifications()
        ->where('type', 'learning.welcome')
        ->count())->toBe(1);
});

test('a learner can mark only their notifications as read', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $user->notify(new LearningActivityNotification(
        notificationType: 'learning.welcome',
        title: 'Bienvenue sur IRMA Learning',
        message: 'Votre espace apprenant est prêt.',
    ));
    $otherUser->notify(new LearningActivityNotification(
        notificationType: 'learning.welcome',
        title: 'Bienvenue sur IRMA Learning',
        message: 'Votre espace apprenant est prêt.',
    ));

    $notification = $user->notifications()->firstOrFail();
    $otherNotification = $otherUser->notifications()->firstOrFail();

    $this->actingAs($user)
        ->post(route('notifications.read', $notification))
        ->assertRedirect();

    expect($notification->refresh()->read_at)->not->toBeNull();

    $this->actingAs($user)
        ->post(route('notifications.read', $otherNotification))
        ->assertForbidden();

    expect($otherNotification->refresh()->read_at)->toBeNull();
});

test('a learner can mark all notifications as read', function () {
    $user = User::factory()->create();

    $user->notify(new LearningActivityNotification(
        notificationType: 'learning.welcome',
        title: 'Bienvenue sur IRMA Learning',
        message: 'Votre espace apprenant est prêt.',
    ));
    $user->notify(new LearningActivityNotification(
        notificationType: 'learning.payment-confirmed',
        title: 'Paiement confirmé',
        message: 'Votre formation est accessible.',
        tone: 'success',
    ));

    $this->actingAs($user)
        ->post(route('notifications.read-all'))
        ->assertRedirect();

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});
