<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('can create a user with all options provided', function () {
    artisan('app:create-user', [
        '--name' => 'John Doe',
        '--email' => 'john@example.com',
        '--password' => 'password123',
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('User [John Doe] created successfully');

    assertDatabaseCount('users', 1);

    assertDatabaseHas('users', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

it('hashes the password when creating a user', function () {
    artisan('app:create-user', [
        '--name' => 'Jane Doe',
        '--email' => 'jane@example.com',
        '--password' => 'secret123',
    ])->assertSuccessful();

    $user = User::where('email', 'jane@example.com')->first();

    expect($user)->not->toBeNull();
    expect(Hash::check('secret123', $user->password))->toBeTrue();
});

it('creates multiple users with different data', function () {
    artisan('app:create-user', [
        '--name' => 'User One',
        '--email' => 'user1@example.com',
        '--password' => 'password1',
    ])->assertSuccessful();

    artisan('app:create-user', [
        '--name' => 'User Two',
        '--email' => 'user2@example.com',
        '--password' => 'password2',
    ])->assertSuccessful();

    assertDatabaseCount('users', 2);

    assertDatabaseHas('users', ['email' => 'user1@example.com']);
    assertDatabaseHas('users', ['email' => 'user2@example.com']);
});

it('assigns sequential IDs to created users', function () {
    artisan('app:create-user', [
        '--name' => 'First User',
        '--email' => 'first@example.com',
        '--password' => 'password',
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('User [First User] created successfully with ID: 1');

    artisan('app:create-user', [
        '--name' => 'Second User',
        '--email' => 'second@example.com',
        '--password' => 'password',
    ])
        ->assertSuccessful()
        ->expectsOutputToContain('User [Second User] created successfully with ID: 2');
});
