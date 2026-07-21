<?php

declare(strict_types=1);

use App\Enums\EnrollmentPaymentEnum;
use App\Enums\EnrollmentStatusEnum;
use App\Enums\UserProgressEnum;
use App\Enums\UserStatusEnum;
use App\Mcp\Servers\LearningServer;
use App\Mcp\Tools\MyCertificatesTool;
use App\Mcp\Tools\MyLearningProgressTool;
use App\Mcp\Tools\MyNextLearningStepTool;
use App\Mcp\Tools\SearchFormationsTool;
use App\Models\Certificate;
use App\Models\Chapter;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\Formation;
use App\Models\Section;
use App\Models\User;
use App\Models\UserProgress;
use Laravel\Sanctum\PersonalAccessToken;

/**
 * @return array<string, mixed>
 */
function initializeMcpRequest(): array
{
    return [
        'jsonrpc' => '2.0',
        'id' => 'test-initialize',
        'method' => 'initialize',
        'params' => [
            'protocolVersion' => '2025-06-18',
            'capabilities' => [],
            'clientInfo' => [
                'name' => 'irma-learning-test',
                'version' => '1.0.0',
            ],
        ],
    ];
}

test('the web MCP endpoint requires a Sanctum bearer token', function () {
    $this->postJson('/mcp/learning', initializeMcpRequest())
        ->assertUnauthorized();
});

test('a Sanctum bearer token can initialize the learning server', function () {
    $user = User::factory()->create();
    $token = $user->createToken('learning-mcp', ['mcp:read'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/mcp/learning', initializeMcpRequest())
        ->assertOk()
        ->assertJsonPath('result.serverInfo.name', 'IRMA Learning');
});

test('the learning server rejects a token without the read ability', function () {
    $user = User::factory()->create();
    $token = $user->createToken('unrelated-token', ['profile:read'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/mcp/learning', initializeMcpRequest())
        ->assertForbidden();
});

test('the learning server rejects a token belonging to a suspended user', function () {
    $user = User::factory()->create(['status' => UserStatusEnum::BANNED]);
    $token = $user->createToken('suspended-user', ['mcp:read'])->plainTextToken;

    $this->withToken($token)
        ->postJson('/mcp/learning', initializeMcpRequest())
        ->assertForbidden();
});

test('the MCP token command only creates a read token for an active user', function () {
    $user = User::factory()->create();

    $this->artisan('app:issue-mcp-token', [
        'email' => $user->email,
        '--expires-in' => 7,
    ])->assertSuccessful();

    expect(PersonalAccessToken::query()
        ->where('tokenable_id', $user->id)
        ->where('tokenable_type', User::class)
        ->first())
        ->not->toBeNull()
        ->and(PersonalAccessToken::query()->first()?->abilities)->toBe(['mcp:read']);
});

test('the catalogue tool only returns active formations', function () {
    $user = User::factory()->create();
    $visible = Formation::factory()->create([
        'title' => 'Python pour débutants',
        'is_active' => true,
    ]);
    Formation::factory()->create([
        'title' => 'Python archivée',
        'is_active' => false,
    ]);

    $response = LearningServer::actingAs($user)->tool(SearchFormationsTool::class, [
        'query' => 'Python',
    ]);

    $response
        ->assertOk()
        ->assertSee($visible->title)
        ->assertDontSee('Python archivée');
});

test('the progress tool only exposes the authenticated learner enrollments', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $formation = Formation::factory()->create(['is_active' => true]);

    Enrollment::factory()->for($user)->for($formation)->create([
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 42,
    ]);
    Enrollment::factory()->for($otherUser)->for($formation)->create([
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
        'progress_percentage' => 99,
    ]);

    $response = LearningServer::actingAs($user)->tool(MyLearningProgressTool::class);

    $response
        ->assertOk()
        ->assertSee('42')
        ->assertDontSee('99');
});

test('the next step tool returns a section exam before the following section', function () {
    $user = User::factory()->create();
    $formation = Formation::factory()->create(['is_active' => true]);
    $firstSection = Section::factory()->for($formation)->create(['order_position' => 1]);
    $secondSection = Section::factory()->for($formation)->create(['order_position' => 2]);
    $firstChapter = Chapter::factory()->for($firstSection)->create(['order_position' => 1]);
    Chapter::factory()->for($secondSection)->create(['order_position' => 1]);
    $exam = Exam::factory()->forSection($firstSection)->active()->create();

    Enrollment::factory()->for($user)->for($formation)->create([
        'status' => EnrollmentStatusEnum::ACTIVE,
        'payment_status' => EnrollmentPaymentEnum::FREE,
    ]);
    UserProgress::create([
        'user_id' => $user->id,
        'trackable_type' => Chapter::class,
        'trackable_id' => $firstChapter->id,
        'status' => UserProgressEnum::COMPLETED,
        'progress_percentage' => 100,
        'completed_at' => now(),
    ]);

    $response = LearningServer::actingAs($user)->tool(MyNextLearningStepTool::class, [
        'formation_id' => $formation->id,
    ]);

    $response
        ->assertOk()
        ->assertSee('section_exam')
        ->assertSee((string) $exam->id)
        ->assertDontSee((string) $secondSection->id);
});

test('the certificates tool only returns certificates belonging to the authenticated learner', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $certificate = Certificate::factory()->for($user)->create();
    $otherCertificate = Certificate::factory()->for($otherUser)->create();

    $response = LearningServer::actingAs($user)->tool(MyCertificatesTool::class);

    $response
        ->assertOk()
        ->assertSee($certificate->certificate_number)
        ->assertDontSee($otherCertificate->certificate_number);
});
