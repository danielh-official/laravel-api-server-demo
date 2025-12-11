<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'website' => 'nullable|url|max:255',
            'is_featured' => 'nullable|boolean',
            'level' => 'nullable|string|max:255',
            'image' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'specialties' => 'nullable|array',
            'specialties.*' => 'required|string|max:255',
        ]);

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
    public function update(Request $request, Partner $partner)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'website' => 'nullable|url|max:255',
            'is_featured' => 'nullable|boolean',
            'level' => 'nullable|string|max:255',
            'image' => 'nullable|url|max:255',
            'location' => 'nullable|string|max:255',
            'specialties' => 'nullable|array',
            'specialties.*' => 'required|string|max:255',
        ]);

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
