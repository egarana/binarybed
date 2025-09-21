<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Property;
use App\Models\User;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('Dashboard/Index', []);
    }
}
