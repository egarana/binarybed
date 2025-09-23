<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Reservation;
use App\Models\Unit;
use App\Models\Country;
use App\Models\Availability;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Inertia\Response;
use Inertia\Inertia;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ReservationController extends Controller
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

        $dateOf = $request->query('dateOf', 'check_in'); // default
        if (!in_array($dateOf, ['check_in', 'check_out', 'booked_on'])) {
            $dateOf = 'check_in'; // fallback to safe column
        }

        // Validate perPage
        $perPage = intval($request->query('perPage', 20));
        if (!in_array($perPage, [10, 20, 30, 40, 50])) {
            $perPage = 20;
        }

        // Define allowed sorts
        $allowedSorts = [
            'first_name',
            'check_in',
            'check_out',
            'booked_on',
            AllowedSort::callback('unit', function ($query, $direction, $unit) {
                // ✅ Normalize direction (fixes Spatie bug)
                $direction = in_array(strtolower($direction), ['asc', 'desc'])
                    ? strtolower($direction)
                    : (request()->query('sort') === "-{$unit}" ? 'desc' : 'asc');

                // ✅ Join properties table if not already joined
                if (!collect($query->getQuery()->joins ?? [])->pluck('table')->contains('units')) {
                    $query->leftJoin('units', 'reservations.unit_id', '=', 'units.id');
                }

                // ✅ Avoid column conflict
                $query->select('reservations.*');

                // ✅ Sort by property name
                $query->orderBy('units.name', $direction);
            }),
            AllowedSort::callback('rate', function ($query, $direction, $rate) {
                // ✅ Normalize direction (fixes Spatie bug)
                $direction = in_array(strtolower($direction), ['asc', 'desc'])
                    ? strtolower($direction)
                    : (request()->query('sort') === "-{$rate}" ? 'desc' : 'asc');

                // ✅ Join properties table if not already joined
                if (!collect($query->getQuery()->joins ?? [])->pluck('table')->contains('rates')) {
                    $query->leftJoin('rates', 'reservations.rate_id', '=', 'rates.id');
                }

                // ✅ Avoid column conflict
                $query->select('reservations.*');

                // ✅ Sort by property name
                $query->orderBy('rates.name', $direction);
            }),
            'created_at',
            'updated_at',
        ];

        // Extract sort keys for validation
        $allowedSortKeys = [
            'first_name',
            'check_in',
            'check_out',
            'booked_on',
            'unit',
            'rate',
            'created_at',
            'updated_at',
        ];

        // Validate and sanitize sort parameter
        $sort = $request->query('sort');
        if ($sort && !in_array(ltrim($sort, '-'), $allowedSortKeys)) {
            // Remove invalid sort from query
            $request->query->remove('sort');
            $sort = null;
        }

        // Build query
        $reservations = QueryBuilder::for(Reservation::class, $request)
            ->allowedFilters([
                AllowedFilter::callback('search', function ($query, $value) {
                    $terms = explode(' ', $value);

                    $query->where(function ($q) use ($terms) {
                        foreach ($terms as $term) {
                            $q->where(function ($q2) use ($term) {
                                $q2->where('first_name', 'like', "%{$term}%")
                                ->orWhere('last_name', 'like', "%{$term}%")
                                ->orWhereHas('unit', function ($u) use ($term) {
                                    $u->where('name', 'like', "%{$term}%")
                                        ->orWhereHas('property', function ($p) use ($term) {
                                            $p->where('name', 'like', "%{$term}%");
                                        });
                                })
                                ->orWhereHas('rate', function ($r) use ($term) {
                                    $r->where('name', 'like', "%{$term}%");
                                });
                            });
                        }
                    });
                }),
            ])
            ->allowedSorts($allowedSorts)
            ->with(['unit.property', 'rate'])
            ->when($request->filled('from') || $request->filled('until'), function ($query) use ($request, $dateOf) {
                $from = $request->query('from');
                $until = $request->query('until');

                $query->where(function ($q) use ($from, $until, $dateOf) {
                    if ($from) {
                        $q->whereDate("reservations.{$dateOf}", '>=', $from);
                    }
                    if ($until) {
                        $q->whereDate("reservations.{$dateOf}", '<=', $until);
                    }
                });
            })
            ->when(!$sort, function ($query) {
                // ✅ Default: latest by ID if no sort param
                $query->orderByDesc('id');
            })
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations,
            'perPage' => $perPage,
            'search' => $request->input('search'),
            'sort' => $sort,
            'dateOf' => $dateOf,
            'fromDate' => $request->query('from'),
            'toDate' => $request->query('until'),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        // Normalize 'search' to 'filter[search]'
        if ($request->filled('search')) {
            $request->merge([
                'filter' => ['search' => $request->input('search')]
            ]);
        }

        // Grab the search value (after normalization)
        $search = $request->input('filter.search');

        // Default empty collections
        $units = collect();
        $countries = collect();
        $disabledDates = collect();
        $datesWithQty = collect();
        $minQty = null;
        
        if (!empty($search)) {
            $units = QueryBuilder::for(Unit::class)
                ->with(['property', 'rates'])
                // ->allowedFilters([
                //     AllowedFilter::partial('search', 'name'),
                // ])
                ->allowedFilters([
                    AllowedFilter::callback('search', function ($query, $value) {
                        $terms = explode(' ', $value);

                        $query->where(function ($q) use ($terms) {
                            foreach ($terms as $term) {
                                $q->where(function ($q2) use ($term) {
                                    $q2->where('name', 'like', "%{$term}%")
                                        ->orWhereHas('property', function ($u) use ($term) {
                                            $u->where('name', 'like', "%{$term}%");
                                        });
                                });
                            }
                        });
                    }),
                ])
                ->orderBy('name')
                ->get(['id', 'name', 'property_id'])
                ->map(function ($unit) {
                    return [
                        'value' => (string) $unit->id,
                        'label' => $unit->name . ' (' . ($unit->property->name ?? 'No property') . ')',
                        'rates' => $unit->rates->map(function ($rate) {
                            return [
                                'value' => (string) $rate->id,
                                // 'label' => $rate->name . ' (' . 'Rp ' . number_format($rate->price ?? 0, 0, ',', '.') . ')',
                                'label' => $rate->name,
                            ];
                        }),
                    ];
                });
        }

        if (!empty($search)) {
            $countries = QueryBuilder::for(Country::class)
                ->allowedFilters([
                    AllowedFilter::partial('search', 'name'),   // supports search
                ])
                ->orderBy('name')
                ->get(['id', 'iso2', 'name', 'dial_code']) // pick only what you need
                ->map(function ($country) {
                    return [
                        'country' => $country->iso2,
                        'countryName' => $country->name,
                        'code' => $country->dial_code,
                    ];
                });
        }

        // ✅ If unit_id provided, fetch disabled dates
        if ($request->filled('unit_id')) {
            $disabledDates = Availability::query()
                ->where('unit_id', $request->unit_id)
                ->where(function ($q) {
                    $q->where('is_open', false)
                    ->orWhere('qty', '<=', 0);
                })
                ->pluck('date')
                ->values();
        }

        if ($request->filled('unit_id')) {
            $unit = Unit::with('rates')->find($request->unit_id);

            if ($unit) {
                $units = collect([[
                    'value' => (string) $unit->id,
                    'label' => $unit->name . ' (' . ($unit->property->name ?? 'No property') . ')',
                    'rates' => $unit->rates->map(function ($rate) use ($request) {
                        // optional: filter by availability check_in - check_out
                        return [
                            'value' => (string) $rate->id,
                            // 'label' => $rate->name . ' (' . 'Rp ' . number_format($rate->price ?? 0, 0, ',', '.') . ')',
                            'label' => $rate->name,
                        ];
                    }),
                ]]);
            }
        }

        if ($request->filled('unit_id') && $request->filled(['check_in', 'check_out'])) {
            $unit = Unit::find($request->unit_id);

            if ($unit) {
                $defaultQty = $unit->qty ?? 0;

                $checkIn = Carbon::parse($request->check_in);
                $checkOut = Carbon::parse($request->check_out);

                $availabilities = Availability::query()
                    ->where('unit_id', $unit->id)
                    ->whereBetween('date', [$checkIn, $checkOut->copy()->subDay()])
                    ->orderByRaw("CASE WHEN qty IS NOT NULL THEN 0 ELSE 1 END")
                    ->orderBy('id')
                    ->get()
                    ->unique('date')
                    ->keyBy('date');

                $period = CarbonPeriod::create($checkIn, $checkOut->copy()->subDay());
                $datesWithQty = collect();

                foreach ($period as $date) {
                    $availability = $availabilities->get($date->toDateString());

                    $datesWithQty->push([
                        'date' => $date->toDateString(),
                        'qty'  => $availability
                            ? (
                                $availability->is_open
                                    ? ($availability->qty !== null ? $availability->qty : $defaultQty)
                                    : 0
                            )
                            : $defaultQty,
                    ]);
                }

                // cari nilai qty terkecil
                $minQty = $datesWithQty->min('qty');
            }
        }

        return Inertia::render('Reservations/Create', [
            'units' => $units,
            'countries' => $countries,
            'disabledDates' => $disabledDates,
            'datesWithQty' => $datesWithQty,
            'minQty'       => $minQty,
        ]);
    }

    public function store(StoreReservationRequest $request)
    {
        $validated = $request->validated();

        $checkIn  = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        $unit     = Unit::findOrFail($validated['unit']['value']);

        return DB::transaction(function () use ($validated, $checkIn, $checkOut, $unit) {

            // 1. Cari overlapping reservations
            $overlappingOrders = Reservation::where('unit_id', $unit->id)
                ->where(function ($q) use ($checkIn, $checkOut) {
                    $q->whereBetween('check_in', [$checkIn, $checkOut->copy()->subDay()])
                    ->orWhereBetween('check_out', [$checkIn->copy()->addDay(), $checkOut])
                    ->orWhere(function ($q2) use ($checkIn, $checkOut) {
                        $q2->where('check_in', '<=', $checkIn)
                            ->where('check_out', '>=', $checkOut);
                    });
                })
                ->pluck('sort_order')
                ->unique()
                ->sort()
                ->values();

            // 2. Cari slot kosong
            $sortOrder = null;
            for ($i = 1; $i <= $unit->qty; $i++) {
                if (! $overlappingOrders->contains($i)) {
                    $sortOrder = $i;
                    break;
                }
            }

            if (! $sortOrder) {
                return back()->withErrors(['unit' => 'No available rooms for this period.']);
            }

            // 3. Buat reservation
            $reservation = Reservation::create([
                'unit_id'    => $unit->id,
                'rate_id'    => $validated['rate']['value'],
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'email'      => $validated['email'],
                'phone'      => $validated['phone'],
                'check_in'   => $checkIn,
                'check_out'  => $checkOut,
                'booked_on'  => now(),
                'sort_order' => $sortOrder,
            ]);

            // 4. Reduce availability
            $period = CarbonPeriod::create($checkIn, $checkOut->copy()->subDay());
            foreach ($period as $date) {
                $this->adjustAvailability($unit, $date, 'reduce');
            }

            return redirect()->route('reservations.index');
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation $reservation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation, Request $request)
    {
        // Normalize 'search' to 'filter[search]'
        if ($request->filled('search')) {
            $request->merge([
                'filter' => ['search' => $request->input('search')]
            ]);
        }

        // Grab the search value (after normalization)
        $search = $request->input('filter.search');

        // Default empty collections
        $units = collect();
        $countries = collect();
        $disabledDates = collect();
        
        if (!empty($search)) {
            $units = QueryBuilder::for(Unit::class)
                ->with(['property', 'rates'])
                ->allowedFilters([
                    // AllowedFilter::partial('search', 'name'),
                    AllowedFilter::callback('search', function ($query, $value) {
                        $terms = explode(' ', $value);

                        $query->where(function ($q) use ($terms) {
                            foreach ($terms as $term) {
                                $q->where(function ($q2) use ($term) {
                                    $q2->where('name', 'like', "%{$term}%")
                                        ->orWhereHas('property', function ($u) use ($term) {
                                            $u->where('name', 'like', "%{$term}%");
                                        });
                                });
                            }
                        });
                    }),
                ])
                ->orderBy('name')
                ->get(['id', 'name', 'property_id'])
                ->map(function ($unit) {
                    return [
                        'value' => (string) $unit->id,
                        'label' => $unit->name . ' (' . ($unit->property->name ?? 'No property') . ')',
                        'rates' => $unit->rates->map(function ($rate) {
                            return [
                                'value' => (string) $rate->id,
                                'label' => $rate->name . ' (' . 'Rp ' . number_format($rate->price ?? 0, 0, ',', '.') . ')',
                            ];
                        }),
                    ];
                });
        }

        if (!empty($search)) {
            $countries = QueryBuilder::for(Country::class)
                ->allowedFilters([
                    AllowedFilter::partial('search', 'name'),   // supports search
                ])
                ->orderBy('name')
                ->get(['id', 'iso2', 'name', 'dial_code']) // pick only what you need
                ->map(function ($country) {
                    return [
                        'country' => $country->iso2,
                        'countryName' => $country->name,
                        'code' => $country->dial_code,
                    ];
                });
        }

        // Use unit_id from request OR fallback to reservation->unit_id
        $unitId = $request->unit_id ?? $reservation->unit_id;
        if (!empty($unitId)) {
            $disabledDates = Availability::query()
                ->where('unit_id', $unitId)
                ->where(function ($q) {
                    $q->where('is_open', false)
                    ->orWhere('qty', '<=', 0);
                })
                ->pluck('date')
                ->values();
        }

        // Eager load related models if needed
        $reservation->load('unit', 'rate');

        // Transform to frontend format
        $reservationData = [
            'id' => $reservation->id,
            'unit' => $reservation->unit ? [
                'value' => (string) $reservation->unit->id,
                'label' => $reservation->unit->name . 
                    ($reservation->unit->property ? ' (' . $reservation->unit->property->name . ')' : ''),

                // Include unit rates
                'rates' => $reservation->unit->rates->map(function ($rate) {
                    return [
                        'value' => (string) $rate->id,
                        'label' => $rate->name . ' (Rp ' . number_format($rate->price, 0, ',', '.') . ')',
                    ];
                })->toArray(),
            ] : null,
            'rate' => $reservation->rate ? [
                'value' => (string) $reservation->rate->id,
                'label' => $reservation->rate->name . ' (Rp ' . number_format($reservation->rate->price, 0, ',', '.') . ')',
            ] : null,
            'first_name' => $reservation->first_name,
            'last_name' => $reservation->last_name,
            'email' => $reservation->email,
            'phone' => $this->transformPhone($reservation->phone),
            'check_in' => $reservation->check_in,
            'check_out' => $reservation->check_out,
        ];

        return Inertia::render('Reservations/Edit', [
            'reservation' => $reservationData,
            'units' => $units,
            'countries' => $countries,
            'disabledDates' => $disabledDates,
        ]);
    }

    /**
     * Transform phone JSON into frontend PhoneField format
     */
    private function transformPhone($phone)
    {
        if (!$phone) {
            return null;
        }

        return [
            'country' => [
                'country' => $phone['country']['country'] ?? '',
                'countryName' => $phone['country']['countryName'] ?? '',
                'code' => $phone['country']['code'] ?? '',
            ],
            'number' => $phone['number'] ?? '',
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreReservationRequest $request, Reservation $reservation)
    {
        $validated = $request->validated();

        $oldCheckIn  = Carbon::parse($reservation->check_in);
        $oldCheckOut = Carbon::parse($reservation->check_out);
        $newCheckIn  = Carbon::parse($validated['check_in']);
        $newCheckOut = Carbon::parse($validated['check_out']);
        $unit        = Unit::findOrFail($validated['unit']['value']);

        return DB::transaction(function () use ($reservation, $validated, $oldCheckIn, $oldCheckOut, $newCheckIn, $newCheckOut, $unit) {

            // 1. Restore old availability
            $oldPeriod = CarbonPeriod::create($oldCheckIn, $oldCheckOut->copy()->subDay());
            foreach ($oldPeriod as $date) {
                $this->adjustAvailability($unit, $date, 'restore');
            }

            // 2. Cari overlapping reservations (exclude current)
            $overlappingOrders = Reservation::where('unit_id', $unit->id)
                ->where('id', '!=', $reservation->id)
                ->where(function ($q) use ($newCheckIn, $newCheckOut) {
                    $q->whereBetween('check_in', [$newCheckIn, $newCheckOut->copy()->subDay()])
                    ->orWhereBetween('check_out', [$newCheckIn->copy()->addDay(), $newCheckOut])
                    ->orWhere(function ($q2) use ($newCheckIn, $newCheckOut) {
                        $q2->where('check_in', '<=', $newCheckIn)
                            ->where('check_out', '>=', $newCheckOut);
                    });
                })
                ->pluck('sort_order')
                ->unique()
                ->sort()
                ->values();

            // 3. Cari slot kosong
            $sortOrder = null;
            for ($i = 1; $i <= $unit->qty; $i++) {
                if (! $overlappingOrders->contains($i)) {
                    $sortOrder = $i;
                    break;
                }
            }

            if (! $sortOrder) {
                return back()->withErrors(['unit' => 'No available rooms for this period.']);
            }

            // 4. Update reservation
            $reservation->update([
                'unit_id'    => $unit->id,
                'rate_id'    => $validated['rate']['value'],
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
                'email'      => $validated['email'],
                'phone'      => $validated['phone'],
                'check_in'   => $newCheckIn,
                'check_out'  => $newCheckOut,
                // 'sort_order' => $sortOrder, // bisa diaktifkan kalau slot perlu update
            ]);

            // 5. Reduce new availability
            $newPeriod = CarbonPeriod::create($newCheckIn, $newCheckOut->copy()->subDay());
            foreach ($newPeriod as $date) {
                $this->adjustAvailability($unit, $date, 'reduce');
            }

            return redirect()->route('reservations.index');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        $reservation->delete();
    }

    /**
     * Adjust availability for a given unit & date.
     * Mode: 'reduce' = kurangi qty, 'restore' = tambah qty.
     */
    private function adjustAvailability(Unit $unit, Carbon $date, string $mode = 'reduce'): void
    {
        $availability = Availability::firstOrNew([
            'unit_id' => $unit->id,
            'date'    => $date->toDateString(),
            'rate_id' => null, // hanya base row
        ]);

        if (! $availability->exists || $availability->qty === null) {
            $availability->qty = $unit->qty;
            $availability->is_open = true;
        }

        if ($mode === 'reduce') {
            $availability->qty = max(0, (int) $availability->qty - 1);
        } elseif ($mode === 'restore') {
            $availability->qty = min($unit->qty, (int) $availability->qty + 1);
        }

        $availability->save();
    }

}
