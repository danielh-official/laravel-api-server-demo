<?php

use App\Models\User;
use Laravel\Sanctum\PersonalAccessToken;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('can give a user a token with abilities', function () {
    $user = User::factory()->create(['name' => 'John Doe']);

    artisan('app:give-user-token', [
        'user' => $user->id,
        '--abilities' => ['view-partners', 'edit-partners'],
        '--name' => 'Test Token',
    ])
        ->assertSuccessful()
        ->expectsOutputToContain("Token created successfully for user [{$user->name}]")
        ->expectsOutputToContain('Test Token')
        ->expectsOutputToContain('view-partners, edit-partners');

    assertDatabaseCount('personal_access_tokens', 1);

    assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'tokenable_type' => User::class,
        'name' => 'Test Token',
    ]);
});

it('creates a token with the correct abilities', function () {
    $user = User::factory()->create();

    artisan('app:give-user-token', [
        'user' => $user->id,
        '--abilities' => ['view-partners'],
        '--name' => 'View Only Token',
    ])->assertSuccessful();

    $token = PersonalAccessToken::where('tokenable_id', $user->id)->first();

    expect($token)->not->toBeNull();
    expect($token->abilities)->toBe(['view-partners']);
    expect($token->name)->toBe('View Only Token');
});

it('returns a plain text token in the output', function () {
    $user = User::factory()->create();

    artisan('app:give-user-token', [
        'user' => $user->id,
        '--abilities' => ['view-partners'],
        '--name' => 'API Token',
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('Please save this token');
});

it('can create multiple tokens for the same user', function () {
    $user = User::factory()->create();

    artisan('app:give-user-token', [
        'user' => $user->id,
        '--abilities' => ['view-partners'],
        '--name' => 'First Token',
    ])->assertSuccessful();

    artisan('app:give-user-token', [
        'user' => $user->id,
        '--abilities' => ['edit-partners'],
        '--name' => 'Second Token',
    ])->assertSuccessful();

    assertDatabaseCount('personal_access_tokens', 2);

    $tokens = PersonalAccessToken::where('tokenable_id', $user->id)->get();

    expect($tokens)->toHaveCount(2);
    expect($tokens->pluck('name')->toArray())->toContain('First Token', 'Second Token');
});

it('fails when user does not exist', function () {
    artisan('app:give-user-token', [
        'user' => 999,
        '--abilities' => ['view-partners'],
        '--name' => 'Test Token',
    ])
        ->assertFailed()
        ->expectsOutputToContain('User with ID 999 not found');

    assertDatabaseCount('personal_access_tokens', 0);
});

it('can create tokens with different ability combinations', function () {
    $user = User::factory()->create();

    artisan('app:give-user-token', [
        'user' => $user->id,
        '--abilities' => ['view-partners', 'edit-partners'],
        '--name' => 'Full Access',
    ])->assertSuccessful();

    $token = PersonalAccessToken::where('tokenable_id', $user->id)
        ->where('name', 'Full Access')
        ->first();

    expect($token)->not->toBeNull();
    expect($token->abilities)->toBe(['view-partners', 'edit-partners']);
});

it('displays token information in the output', function () {
    $user = User::factory()->create(['name' => 'Jane Smith']);

    artisan('app:give-user-token', [
        'user' => $user->id,
        '--abilities' => ['view-partners', 'edit-partners'],
        '--name' => 'My API Token',
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('My API Token')
        ->expectsOutputToContain('view-partners, edit-partners');
});

test('abilities is required when creating a token', function () {
    $user = User::factory()->create();

    artisan('app:give-user-token', [
        'user' => $user->id,
        '--name' => 'My API Token',
        '--no-interaction' => true,
    ])
        ->assertFailed();

    assertDatabaseCount('personal_access_tokens', 0);
});
