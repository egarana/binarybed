<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Http\Requests\UpdateReservationRequest;
use App\Models\Reservation;
use App\Models\ReservationSlot;
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
use Illuminate\Support\Str;

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
            'booked_on',
            'status',
            'total_price',
            'payment_status',
            'reservation_code',
        ];

        // Extract sort keys for validation
        $allowedSortKeys = [
            'first_name',
            'check_in',
            'check_out',
            'unit',
            'booked_on',
            'status',
            'total_price',
            'payment_status',
            'reservation_code',
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
                                $q2->whereAny([
                                    'reservation_code',
                                    'first_name',
                                    'last_name',
                                    'status',
                                    'payment_status',
                                ], 'like', "%{$term}%")
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
        $reservationDays = collect();
        $minQty = null;
        $totalPrices = collect();
        
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
                ->get(['id', 'property_id', 'name', 'qty', 'type', 'occupancy'])
                ->map(function ($unit) {
                    return [
                        'value'      => (string) $unit->id,
                        'label'      => $unit->name . ' (' . ($unit->property->name ?? 'No property') . ')',
                        'qty'        => $unit->qty,
                        'type'       => $unit->type,
                        'occupancy'  => $unit->occupancy,
                        'rates'      => $unit->rates->map(function ($rate) {
                            return [
                                'value' => (string) $rate->id,
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
            $unit = Unit::find($request->unit_id);

            // 1. Disabled normal (is_open / qty <= 0)
            $disabledDates = Availability::query()
                ->where('unit_id', $unit->id)
                ->where(function ($q) {
                    $q->where('is_open', false)
                    ->orWhere('qty', '<=', 0);
                })
                ->pluck('date')
                ->values();

            // 2. Tambahin continuity check kalau check_in & check_out ada
            if ($request->filled(['check_in', 'check_out'])) {
                $checkIn = Carbon::parse($request->check_in);
                $checkOut = Carbon::parse($request->check_out);

                $period = CarbonPeriod::create($checkIn, $checkOut->copy()->subDay());

                foreach ($period as $date) {
                    $nextDate = $date->copy()->addDay();

                    $allSlots = range(1, $unit->qty);

                    // slot free hari ini
                    $overlappingToday = ReservationSlot::query()
                        ->whereHas('reservation', fn($q) =>
                            $q->where('unit_id', $unit->id)
                            ->where('check_in', '<=', $date)
                            ->where('check_out', '>', $date)
                        )
                        ->pluck('sort_order')->unique()->toArray();
                    $freeToday = array_diff($allSlots, $overlappingToday);

                    // slot free hari besok
                    $overlappingNext = ReservationSlot::query()
                        ->whereHas('reservation', fn($q) =>
                            $q->where('unit_id', $unit->id)
                            ->where('check_in', '<=', $nextDate)
                            ->where('check_out', '>', $nextDate)
                        )
                        ->pluck('sort_order')->unique()->toArray();
                    $freeNext = array_diff($allSlots, $overlappingNext);

                    // cek continuity
                    $continuous = array_intersect($freeToday, $freeNext);

                    if (empty($continuous)) {
                        // 🔹 kalau gak ada slot yang konsisten, besok jadi batas checkout
                        $disabledDates->push($nextDate->toDateString());
                    }
                }
            }
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
                    ->get()
                    ->groupBy('date');

                $period = CarbonPeriod::create($checkIn, $checkOut->copy()->subDay());
                $reservationDays = collect();

                $taxRate = 0.1; // 10%

                foreach ($period as $date) {
                    $byDate = $availabilities->get($date->toDateString()) ?? collect();

                    // unit-level (rate_id = NULL)
                    $unitRow = $byDate->firstWhere('rate_id', null);

                    foreach ($unit->rates as $rate) {
                        // rate-level (rate_id = specific id)
                        $rateRow = $byDate->firstWhere('rate_id', $rate->id);

                        $isOpen = $rateRow->is_open ?? $unitRow->is_open ?? 1;
                        $qty    = $rateRow->qty     ?? $unitRow->qty     ?? $unit->qty;
                        $price  = $rateRow->price   ?? $rate->price;

                        $tax = (int) round($price * $taxRate);
                        $totalWithTax = $price + $tax;

                        $reservationDays->push([
                            'date'           => $date->toDateString(),
                            'qty'            => $isOpen ? (int) $qty : 0,
                            'rate_id'        => $rate->id,
                            'rate_name'      => $rate->name,
                            'price'          => (int) $price,
                            'tax'            => $tax,           // pajak untuk hari tsb
                            'total_with_tax' => $totalWithTax,
                        ]);
                    }
                }

                // cari nilai qty terkecil
                $minQty = $reservationDays->min('qty');

                $totalPrices = $reservationDays
                    ->groupBy('rate_id')
                    ->map(function ($items, $rateId) {
                        return [
                            'rate_id'   => $rateId,
                            'rate_name' => $items->first()['rate_name'],
                            // sum price sesuai jumlah hari
                            // 'total'     => $items->sum('price'),
                            'total'     => $items->sum('total_with_tax'),
                        ];
                    })
                    ->values();
            }
        }

        return Inertia::render('Reservations/Create', [
            'units'             => $units,
            'countries'         => $countries,
            'disabledDates'     => $disabledDates,
            'reservationDays'   => $reservationDays,
            'minQty'            => $minQty,
            'totalPrices'       => $totalPrices,
        ]);
    }

    public function store(StoreReservationRequest $request)
    {
        $validated = $request->validated();

        $checkIn  = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        $unit     = Unit::findOrFail($validated['unit']['value']);

        return DB::transaction(function () use ($validated, $checkIn, $checkOut, $unit) {

            // Cari overlapping slots
            $overlappingOrders = ReservationSlot::query()
                ->whereHas('reservation', function ($q) use ($unit, $checkIn, $checkOut) {
                    $q->where('unit_id', $unit->id)
                    ->where(function ($q2) use ($checkIn, $checkOut) {
                        $q2->whereBetween('check_in', [$checkIn, $checkOut->copy()->subDay()])
                            ->orWhereBetween('check_out', [$checkIn->copy()->addDay(), $checkOut])
                            ->orWhere(function ($q3) use ($checkIn, $checkOut) {
                                $q3->where('check_in', '<=', $checkIn)
                                    ->where('check_out', '>=', $checkOut);
                            });
                    });
                })
                ->pluck('sort_order')
                ->unique()
                ->sort()
                ->values();

            // Cari slot kosong sejumlah qty
            $slots = [];
            for ($i = 1; $i <= $unit->qty; $i++) {
                if (! $overlappingOrders->contains($i)) {
                    $slots[] = $i;
                }
                if (count($slots) >= $validated['qty']) {
                    break;
                }
            }

            if (count($slots) < $validated['qty']) {
                return back()->withErrors(['unit' => 'No available rooms for this period.']);
            }

            // 🔹 [NEW] Variabel akumulasi untuk breakdown
            $subtotal = 0;
            $taxTotal = 0;
            $serviceTotal = 0;
            $extraTotal = 0;

            $period = CarbonPeriod::create($checkIn, $checkOut->copy()->subDay());
            $taxRate = 0.1; 
            $details = [];

            foreach ($period as $date) {
                $dateStr = $date->toDateString();

                // Cari harga di availability (prioritas)
                $availability = Availability::where('unit_id', $unit->id)
                    ->whereDate('date', $dateStr)
                    ->where(function ($q) use ($validated) {
                        $q->where('rate_id', $validated['rate']['value'])
                        ->orWhereNull('rate_id');
                    })
                    ->orderByRaw('CASE WHEN rate_id IS NULL THEN 2 ELSE 1 END')
                    ->first();

                if ($availability && $availability->price !== null) {
                    $price = $availability->price;
                } else {
                    $price = $unit->rates()->find($validated['rate']['value'])->price ?? 0;
                }

                // Hitung
                $lineSubtotal = $price * $validated['qty'];
                $tax   = (int) round($price * $taxRate) * $validated['qty'];
                $total = $lineSubtotal + $tax;

                $subtotal   += $lineSubtotal;
                $taxTotal   += $tax;
                $serviceTotal += 0;
                $extraTotal   += 0;

                $details[] = [
                    'reservation_id' => $reservation->id ?? null, // diisi setelah create
                    'date'           => $dateStr,
                    'rate_id'        => $validated['rate']['value'], // 🔹 [NEW]
                    'qty'            => $validated['qty'],          // 🔹 [NEW]
                    'base_price'     => $price,
                    'subtotal'       => $lineSubtotal,
                    'tax_amount'     => $tax,
                    'service_fee'    => 0,
                    'extra_charge'   => 0,
                    'total_price'    => $total,
                    'currency'       => $validated['currency'],     // 🔹 [NEW]
                    'created_at'     => now(),
                    'updated_at'     => now(),
                ];
            }

            // 🔹 [CHANGED] Buat reservation setelah breakdown dihitung
            $reservation = Reservation::create([
                'reservation_code'  => $this->generateReservationCode(),
                'unit_id'           => $unit->id,
                'rate_id'           => $validated['rate']['value'],
                'first_name'        => $validated['first_name'],
                'last_name'         => $validated['last_name'],
                'email'             => $validated['email'],
                'phone'             => $validated['phone'],
                'check_in'          => $checkIn,
                'check_out'         => $checkOut,
                'guests'            => $validated['guests'],
                'qty'               => $validated['qty'],
                // 🔹 [CHANGED] isi breakdown
                'subtotal'          => $subtotal,
                'tax_total'         => $taxTotal,
                'service_total'     => $serviceTotal,
                'extra_total'       => $extraTotal,
                'total_price'       => $subtotal + $taxTotal + $serviceTotal + $extraTotal,
                'currency'          => $validated['currency'],
                'status'            => $validated['status']['value'] ?? 'pending',
                'payment_status'    => $validated['payment_status']['value'] ?? 'unpaid',
                'booked_on'         => now(),
                'notes'             => $validated['notes'] ?? null,
                'source'            => $validated['source']['value'] ?? 'direct',
            ]);

            // Update reservation_id di details
            foreach ($details as &$d) {
                $d['reservation_id'] = $reservation->id;
            }
            unset($d);

            DB::table('reservation_details')->insert($details);

            // Simpan slot-slot yang dipakai
            foreach ($slots as $slot) {
                $reservation->slots()->create([
                    'sort_order' => $slot,
                ]);
            }

            // Adjust availability
            foreach ($period as $date) {
                $this->adjustAvailability($unit, $date, 'reduce', $validated['qty']);
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
                ->get(['id', 'property_id', 'name', 'qty'])
                ->map(function ($unit) {
                    return [
                        'value' => (string) $unit->id,
                        'label' => $unit->name . ' (' . ($unit->property->name ?? 'No property') . ')',
                        'qty'   => $unit->qty,
                        'type'  => $unit->type,
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
            foreach ($oldPeriod as $date) {
                $this->adjustAvailability($unit, $date, 'restore', $reservation->qty);
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
                'reservation_code' => $this->generateReservationCode(),
                'unit_id'          => $unit->id,
                'rate_id'          => $validated['rate']['value'],
                'first_name'       => $validated['first_name'],
                'last_name'        => $validated['last_name'],
                'email'            => $validated['email'],
                'phone'            => $validated['phone'],
                'check_in'         => $newCheckIn,
                'check_out'        => $newCheckOut,
                'guests'           => $validated['guests'],
			    'qty'              => $validated['qty'],
                'total_price'      => $validated['total_price'],
			    'currency'         => $validated['currency'],
                'status'           => $validated['status'] ?? 'pending',
			    'payment_status'   => $validated['payment_status'] ?? 'unpaid',
                // 'sort_order' => $sortOrder, // bisa diaktifkan kalau slot perlu update
            ]);

            // 5. Reduce new availability
            foreach ($newPeriod as $date) {
                $this->adjustAvailability($unit, $date, 'reduce', $validated['qty']);
            }

            return redirect()->route('reservations.index');
        });
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation $reservation)
    {
        // kembalikan stok availability
        $this->restoreReservationAvailability($reservation);

        // hapus slot-slot
        $reservation->slots()->delete();

        // hapus reservation
        $reservation->delete();

        return redirect()->route('reservations.index');
    }

    /**
     * Adjust availability for a given unit & date.
     * Mode: 'reduce' = kurangi qty, 'restore' = tambah qty.
     */
    private function adjustAvailability(Unit $unit, Carbon $date, string $mode = 'reduce', int $amount = 1): void
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
            $availability->qty = max(0, (int) $availability->qty - $amount);
        } elseif ($mode === 'restore') {
            $availability->qty = min($unit->qty, (int) $availability->qty + $amount);
        }

        $availability->save();
    }

    /**
     * Generate a unique 10-character alphanumeric reservation code (A-Z, 0-9)
     */
    protected function generateReservationCode(): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $length = 10;
        $code = '';

        for ($i = 0; $i < $length; $i++) {
            $index = random_int(0, strlen($characters) - 1);
            $code .= $characters[$index];
        }

        // Pastikan unique
        if (Reservation::where('reservation_code', $code)->exists()) {
            return $this->generateReservationCode();
        }

        return $code;
    }

    /**
     * Restore availability untuk seluruh periode reservasi
     */
    private function restoreReservationAvailability(Reservation $reservation): void
    {
        $unit = $reservation->unit;
        $qty  = $reservation->qty;

        $checkIn  = Carbon::parse($reservation->check_in);
        $checkOut = Carbon::parse($reservation->check_out);

        $period = CarbonPeriod::create($checkIn, $checkOut->copy()->subDay());

        foreach ($period as $date) {
            $this->adjustAvailability($unit, $date, 'restore', $qty);
        }
    }

}
