<?php

use App\Models\Partner;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

it('can get a paginated list of partners', function () {
    Partner::factory(20)->create();

    Sanctum::actingAs(User::factory()->create(), ['view-partners']);

    getJson(route('api.partners.index'))
        ->assertOk()
        ->assertJsonCount(15, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'name',
                    'description',
                    'website',
                    'is_featured',
                    'level',
                    'image',
                    'location',
                    'specialties',
                    'created_at',
                    'updated_at',
                ],
            ],
            'links',
            'meta',
        ]);
});

it('cannot get a paginated list of partners if missing view-partners ability', function () {
    Partner::factory(20)->create();

    Sanctum::actingAs(User::factory()->create(), []);

    getJson(route('api.partners.index'))
        ->assertForbidden();
});

it('cannot get a paginated list of partners without authentication', function () {
    Partner::factory(20)->create();

    getJson(route('api.partners.index'))
        ->assertUnauthorized();
});

it('can get the next page of partners', function () {
    Partner::factory(20)->create();

    Sanctum::actingAs(User::factory()->create(), ['view-partners']);

    getJson(route('api.partners.index', [
        'page' => 2,
    ]))
        ->assertOk()
        ->assertJsonCount(5, 'data');
});

it('can change the partners per page', function () {
    Partner::factory(20)->create();

    Sanctum::actingAs(User::factory()->create(), ['view-partners']);

    getJson(route('api.partners.index', [
        'page' => 1,
        'per_page' => 20,
    ]))
        ->assertOk()
        ->assertJsonCount(20, 'data');
});

it('can create a new partner', function () {
    Sanctum::actingAs(User::factory()->create(), ['edit-partners']);

    postJson(route('api.partners.store'), [
        'name' => 'New Partner',
    ])
        ->assertCreated()
        ->assertJsonFragment([
            'name' => 'New Partner',
        ]);

    assertDatabaseCount('partners', 1);

    assertDatabaseHas('partners', [
        'name' => 'New Partner',
    ]);
});

it('cannot create a new partner without the edit-partners ability', function () {
    Sanctum::actingAs(User::factory()->create(), []);

    postJson(route('api.partners.store'), [
        'name' => 'New Partner',
    ])
        ->assertForbidden();

    assertDatabaseCount('partners', 0);
});

it('cannot create a new partner without authentication', function () {
    postJson(route('api.partners.store'), [
        'name' => 'New Partner',
    ])
        ->assertUnauthorized();

    assertDatabaseCount('partners', 0);
});
