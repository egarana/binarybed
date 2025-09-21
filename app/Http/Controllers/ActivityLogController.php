<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;
use Inertia\Response;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // Normalize 'search' to 'filter[search]'
        if ($request->filled('search')) {
            $request->merge([
                'filter' => ['search' => $request->input('search')]
            ]);
        }

        $perPage = intval($request->query('perPage', 20));
        if (!in_array($perPage, [10, 20, 30, 40, 50])) {
            $perPage = 20;
        }

        $query = QueryBuilder::for(Activity::class, $request)
            ->allowedFilters([
                AllowedFilter::partial('search', 'description'),
            ])
            ->allowedSorts([
                'created_at', // allow sorting by creation time
            ]);

        // Apply default sort by latest (descending `created_at`)
        if (!$request->has('sort')) {
            $query->defaultSort('-created_at');
        }

        $activities = $query
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('ActivityLogs/Index', [
            'activities' => $activities,
            'perPage' => $perPage,
            'search' => $request->input('search'),
        ]);
    }
}
