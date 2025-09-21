<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Property;
use App\Models\User;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use App\Models\Reservation;
use App\Models\Unit;

class TestController extends Controller
{
    public function indexTest()
    {
        $dateToCheck = "2025-08-30";

        $reservations =  Reservation::where('unit_id', 3)
            ->where('check_in', '<=', $dateToCheck)
            ->where('check_out', '>', $dateToCheck)
            ->count();

        return response()->json($reservations);
    }

    public function index(Request $request)
    {
        // Normalize 'search' to 'filter[search]'
        if ($request->filled('search')) {
            $request->merge([
                'filter' => ['search' => $request->input('search')]
            ]);
        }

        $users = QueryBuilder::for(User::class)
            ->allowedFilters([
                AllowedFilter::partial('search', 'name'),
            ])
            ->orderBy('name')
            ->get(['id', 'name', 'email'])
            ->map(function ($user) {
                return [
                    'value' => (string) $user->id,
                    'label' => [
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                ];
            });

        return Inertia::render('Test/Index', [
            'users' => $users,
        ]);
    }
}
