<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PartnerRequest;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

/**
 * @group Partners
 */
class PartnerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('abilities:view-partners', only: ['index', 'show']),
            new Middleware('abilities:edit-partners', except: ['index', 'show']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pagination = Partner::paginate(
            perPage: $request->get('per_page', 15),
            page: $request->get('page', 1)
        );

        return $pagination->toResourceCollection();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PartnerRequest $request)
    {
        return Partner::create($request->all())->toResource();
    }

    /**
     * Display the specified resource.
     */
    public function show(Partner $partner)
    {
        return $partner->toResource();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PartnerRequest $request, Partner $partner)
    {
        return $partner->update($request->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Partner $partner)
    {
        $partner->delete();

        return response('Successfully deleted', 204);
    }
}
