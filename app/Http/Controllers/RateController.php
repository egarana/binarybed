<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRateRequest;
use App\Models\Rate;
use App\Models\Unit;

class RateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRateRequest $request)
    {
        $validated = $request->validated();

        $unit = Unit::findOrFail($validated['unit_id']);

        $unit->rates()->create($validated);
    }

    /**
     * Display the specified resource.
     */
    public function show(Rate $rate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Rate $rate)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreRateRequest $request, Rate $rate)
    {
        $validated = $request->validated();

        $rate->update($validated);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Rate $rate)
    {
        $rate->delete();
    }
}
