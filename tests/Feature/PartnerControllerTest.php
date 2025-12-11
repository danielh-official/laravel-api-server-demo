<?php

use App\Models\Partner;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

describe('index', function () {
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
});

describe('show', function () {

    it('can get a single partner', function () {
        $partner = Partner::factory()->create([
            'name' => 'Test Partner',
            'description' => 'Test Description',
        ]);

        Sanctum::actingAs(User::factory()->create(), ['view-partners']);

        getJson(route('api.partners.show', $partner))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $partner->id,
                'name' => 'Test Partner',
                'description' => 'Test Description',
            ]);
    });

    it('cannot get a single partner without the view-partners ability', function () {
        $partner = Partner::factory()->create();

        Sanctum::actingAs(User::factory()->create(), []);

        getJson(route('api.partners.show', $partner))
            ->assertForbidden();
    });

    it('cannot get a single partner without authentication', function () {
        $partner = Partner::factory()->create();

        getJson(route('api.partners.show', $partner))
            ->assertUnauthorized();
    });

});

describe('create', function () {
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

    it('validates partner data', function (array $invalidData, string $errorField) {
        Sanctum::actingAs(User::factory()->create(), ['edit-partners']);

        postJson(route('api.partners.store'), $invalidData)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errorField);

        assertDatabaseEmpty('partners');
    })->with('invalid partner data');

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
});

describe('update', function () {
    it('can update a partner', function () {
        $partner = Partner::factory()->create([
            'name' => 'Original Name',
        ]);

        Sanctum::actingAs(User::factory()->create(), ['edit-partners']);

        putJson(route('api.partners.update', $partner), [
            'name' => 'Updated Name',
        ])
            ->assertOk();

        assertDatabaseHas('partners', [
            'id' => $partner->id,
            'name' => 'Updated Name',
        ]);
    });

    it('validates partner data', function (array $invalidData, string $errorField) {
        $partner = Partner::factory()->create();

        Sanctum::actingAs(User::factory()->create(), ['edit-partners']);

        putJson(route('api.partners.update', $partner), $invalidData)
            ->assertUnprocessable()
            ->assertJsonValidationErrors($errorField);
    })->with('invalid partner data');

    it('cannot update a partner without the edit-partners ability', function () {
        $partner = Partner::factory()->create([
            'name' => 'Original Name',
        ]);

        Sanctum::actingAs(User::factory()->create(), []);

        putJson(route('api.partners.update', $partner), [
            'name' => 'Updated Name',
        ])
            ->assertForbidden();

        assertDatabaseHas('partners', [
            'id' => $partner->id,
            'name' => 'Original Name',
        ]);
    });

    it('cannot update a partner without authentication', function () {
        $partner = Partner::factory()->create([
            'name' => 'Original Name',
        ]);

        putJson(route('api.partners.update', $partner), [
            'name' => 'Updated Name',
        ])
            ->assertUnauthorized();

        assertDatabaseHas('partners', [
            'id' => $partner->id,
            'name' => 'Original Name',
        ]);
    });
});

describe('delete', function () {
    it('can delete a partner', function () {
        $partner = Partner::factory()->create();

        Sanctum::actingAs(User::factory()->create(), ['edit-partners']);

        deleteJson(route('api.partners.destroy', $partner))
            ->assertNoContent();

        assertDatabaseMissing('partners', [
            'id' => $partner->id,
        ]);

        assertDatabaseEmpty('partners');
    });

    it('cannot delete a partner without the edit-partners ability', function () {
        $partner = Partner::factory()->create();

        Sanctum::actingAs(User::factory()->create(), []);

        deleteJson(route('api.partners.destroy', $partner))
            ->assertForbidden();

        assertDatabaseHas('partners', [
            'id' => $partner->id,
        ]);

        assertDatabaseCount('partners', 1);
    });

    it('cannot delete a partner without authentication', function () {
        $partner = Partner::factory()->create();

        deleteJson(route('api.partners.destroy', $partner))
            ->assertUnauthorized();

        assertDatabaseHas('partners', [
            'id' => $partner->id,
        ]);

        assertDatabaseCount('partners', 1);
    });
});

dataset('invalid partner data', [
    'name is required' => [
        ['description' => 'Test'],
        'name',
    ],
    'name must be a string' => [
        ['name' => 123],
        'name',
    ],
    'name max 255 characters' => [
        ['name' => str_repeat('a', 256)],
        'name',
    ],
    'description max 5000 characters' => [
        ['name' => 'Test', 'description' => str_repeat('a', 5001)],
        'description',
    ],
    'website must be a valid URL' => [
        ['name' => 'Test', 'website' => 'not-a-url'],
        'website',
    ],
    'website max 255 characters' => [
        ['name' => 'Test', 'website' => 'https://'.str_repeat('a', 250).'.com'],
        'website',
    ],
    'is_featured must be boolean' => [
        ['name' => 'Test', 'is_featured' => 'not-boolean'],
        'is_featured',
    ],
    'level max 255 characters' => [
        ['name' => 'Test', 'level' => str_repeat('a', 256)],
        'level',
    ],
    'image must be a valid URL' => [
        ['name' => 'Test', 'image' => 'not-a-url'],
        'image',
    ],
    'image max 255 characters' => [
        ['name' => 'Test', 'image' => 'https://'.str_repeat('a', 250).'.com'],
        'image',
    ],
    'location max 255 characters' => [
        ['name' => 'Test', 'location' => str_repeat('a', 256)],
        'location',
    ],
    'specialties must be an array' => [
        ['name' => 'Test', 'specialties' => 'not-an-array'],
        'specialties',
    ],
    'specialties items must be strings' => [
        ['name' => 'Test', 'specialties' => [123]],
        'specialties.0',
    ],
    'specialties items max 255 characters' => [
        ['name' => 'Test', 'specialties' => [str_repeat('a', 256)]],
        'specialties.0',
    ],
]);
