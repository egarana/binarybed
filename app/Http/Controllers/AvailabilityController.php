<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAvailabilityRequest;
use App\Http\Requests\UpdateAvailabilityRequest;
use App\Models\Availability;
use App\Models\Unit;
use Inertia\Inertia;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedSort;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Normalize 'search' to 'filter[search]'
        if ($request->filled('search')) {
            $request->merge([
                'filter' => ['search' => $request->input('search')]
            ]);
        }

        $search = $request->input('filter.search');
        $units = collect();

        if (!empty($search)) {
            $units = QueryBuilder::for(Unit::class)
                ->with(['property', 'rates'])
                ->allowedFilters([
                    AllowedFilter::callback('search', function ($query, $value) {
                        $terms = explode(' ', $value);
                        $query->where(function ($q) use ($terms) {
                            foreach ($terms as $term) {
                                $q->where(function ($q2) use ($term) {
                                    $q2->where('name', 'like', "%{$term}%")
                                        ->orWhereHas('property', function ($p) use ($term) {
                                            $p->where('name', 'like', "%{$term}%");
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
                        'name'  => $unit->name,
                        'qty'   => $unit->qty,
                        'rates' => $unit->rates->map(function ($rate) {
                            return [
                                'value' => (string) $rate->id,
                                'label' => $rate->name,
                                'price' => $rate->price,
                            ];
                        }),
                    ];
                });
        }

        // 🧹 simplified: always pick topUnit by requested unitId, else latest
        $unitId = $request->input('unitId');
        $topUnit = Unit::with(['property', 'rates'])
            ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$unitId])
            ->orderByDesc('id')
            ->first();

        // 🧹 unified selectedUnit default structure
        $selectedUnit = $topUnit ? [
            'value' => (string) $topUnit->id,
            'label' => $topUnit->name . ' (' . ($topUnit->property->name ?? 'No property') . ')',
            'name'  => $topUnit->name,
            'qty'   => $topUnit->qty,
            'rates' => $topUnit->rates->map(fn($rate) => [
                'value' => (string) $rate->id,
                'label' => $rate->name,
                'price' => $rate->price,
            ])->values(),
        ] : [
            'value' => null,
            'label' => null,
            'name'  => null,
            'qty'   => 0,
            'rates' => collect(),
        ];

        // 🧹 date handling: ensure safe ranges
        $fromInput = $request->input('from');
        $toInput   = $request->input('until');

        $fromDate = Carbon::parse($fromInput ?? Carbon::today())
            ->max(Carbon::create(2025, 1, 1)); // 🧹 keep enforced minimum date

        $toDate = Carbon::parse($toInput ?? $fromDate->copy()->addDays(30));
        if ($toDate->lt($fromDate)) {
            $toDate = $fromDate->copy()->addDay();
        }

        $fromDate = $fromDate->toDateString();
        $toDate   = $toDate->toDateString();

        $period = CarbonPeriod::create($fromDate, $toDate);

        $unit = $topUnit ? Unit::with(['reservations'])->find($topUnit->id) : null;
        $seenReservationIds = [];

        // 🧹 prefetch availabilities into keyed collection
        $baseAvailabilities = $topUnit
            ? Availability::where('unit_id', $topUnit->id)
                ->whereBetween('date', [$fromDate, $toDate])
                ->whereNull('rate_id') // 🔹 [CHANGED] ensure only base rows
                ->get(['date', 'is_open', 'qty'])
                ->keyBy('date')
            : collect();

        // prefetch rate availabilities (per-date, per-rate)
        $rateAvailabilities = $topUnit
            ? Availability::where('unit_id', $topUnit->id)
                ->whereBetween('date', [$fromDate, $toDate])
                ->whereNotNull('rate_id')
                ->with('rate') // eager load rate relationship
                ->get()
                ->groupBy('date')
            : collect();
            
        $availabilities = $topUnit
            ? Availability::where('unit_id', $topUnit->id)
                ->whereBetween('date', [$fromDate, $toDate])
                ->get(['date', 'is_open', 'qty'])
                ->keyBy('date')
            : collect();

        $calendarDays = collect($period)->map(function ($date) use (
            $unit,
            &$seenReservationIds,
            $period,
            $baseAvailabilities,
            $rateAvailabilities
        ) {
            $dateStr = $date->toDateString();

            // Base row
            $availability = $baseAvailabilities->get($dateStr);
            $isOpen = $availability->is_open ?? true;
            $qty    = $availability->qty ?? ($unit->qty ?? 0);

            // Attach rate rows for this date
            $ratesForDay = $rateAvailabilities->get($dateStr, collect())->mapWithKeys(function ($row) {
                return [
                    (string) $row->rate_id => [
                        'price' => $row->price,
                    ],
                ];
            });

            $reservationsForDay = array_fill(0, $unit->qty ?? 0, null);
            $activeReservations = collect();

            if ($unit) {
                $activeReservations = $unit->reservations->filter(fn($res) =>
                    $dateStr >= $res->check_in && $dateStr < $res->check_out
                );

                foreach ($activeReservations as $res) {
                    if (in_array($res->id, $seenReservationIds, true)) {
                        continue;
                    }

                    $seenReservationIds[] = $res->id;
                    $slotIndex = ($res->sort_order ?? 1) - 1;

                    if ($slotIndex >= 0 && $slotIndex < count($reservationsForDay)) {
                        $nights_duration = Carbon::parse($res->check_in)->diffInDays(Carbon::parse($res->check_out));
                        $diffFromPeriodStart = Carbon::parse($res->check_in)->diffInDays($period->first(), false);
                        $diffFromPeriodStart = max(0, $diffFromPeriodStart);

                        $btnWidth = $diffFromPeriodStart > 0
                            ? (($nights_duration - $diffFromPeriodStart) * 80) - 4 + 12
                            : ($nights_duration * 80) - 4;

                        $reservationsForDay[$slotIndex] = [
                            'index'             => $slotIndex,
                            'id'                => $res->id,
                            'name'              => $res->first_name . ' ' . $res->last_name,
                            'check_in'          => $res->check_in,
                            'check_out'         => $res->check_out,
                            'booked_on'         => $res->booked_on,
                            'nights_duration'   => $nights_duration,
                            'period_start_diff' => $diffFromPeriodStart,
                            'is_first_day'      => $dateStr === $res->check_in,
                            'is_last_day'       => $dateStr === Carbon::parse($res->check_out)->subDay()->toDateString(),
                            'btn_width'         => $btnWidth . 'px',
                            'sort_order'        => $res->sort_order,
                        ];
                    }
                }
            }

            return [
                'date'               => $dateStr,
                'day'                => $date->format('D'),
                'day_number'         => $date->format('d'),
                'month'              => $date->format('M'),
                'month_full'         => $date->format('F'),
                'year'               => $date->format('Y'),
                'is_weekend'         => $date->isWeekend(),
                'is_month_start'     => $date->day === 1,
                'is_month_end'       => $date->isLastOfMonth(),
                'formatted'          => $date->format('M d, Y'),
                'qty'                => $availability && isset($availability->qty)
                                        ? (int) $availability->qty
                                        : max(0, ($unit->qty ?? 0) - $activeReservations->count()),
                'is_open'            => (bool) $isOpen,
                'reservations_count' => $activeReservations->count(),
                'reservations'       => $reservationsForDay,
                'rates'              => $ratesForDay, // 🔹 [CHANGED] new key for per-day rates
            ];
        })->values();

        return Inertia::render('Calendar/Index', [
            'units'            => $units,
            'selectedUnit'     => $selectedUnit,
            'fromDate'         => $fromDate,
            'toDate'           => $toDate,
            'calendarDays'     => $calendarDays,
            'seenReservationIds' => $seenReservationIds,
        ]);
    }

    public function create() 
    {

    }

    public function store(StoreAvailabilityRequest $request) 
    {

    }

    public function show(Availability $availability) 
    {

    }

    public function edit(Availability $availability) 
    {

    }

    public function update(Request $request, $unitId)
    {
        $unit = Unit::findOrFail($unitId);
        $date = Carbon::parse($request->input('date'))->toDateString();

        $activeReservations = $unit->reservations()
            ->where('check_in', '<=', $date)
            ->where('check_out', '>', $date)
            ->count();

        $capacity = $unit->qty;
        $availableSlots = max(0, $capacity - $activeReservations);

        $validated = $request->validate([
            'date'     => ['required', 'date'],
            'qty'      => ['nullable', 'integer', 'min:0', 'max:' . $availableSlots],
            'is_open'  => ['nullable', 'boolean'],
            'rate_id'  => ['nullable', 'integer', 'exists:rates,id'],
            'price'    => ['nullable', 'numeric', 'min:0'],
        ]);

        // --- CASE 1: Base row (qty / is_open) ---
        if (is_null($validated['rate_id'] ?? null)) {
            $availability = Availability::firstOrNew([
                'unit_id' => $unitId,
                'date'    => $validated['date'],
                'rate_id' => null,
            ]);

            if (! $availability->exists) {
                // default: null biar dihitung ulang di index
                $availability->qty     = null;
                $availability->is_open = true;
            }

            if (array_key_exists('qty', $validated)) {
                $availability->qty = $validated['qty'];
            }

            if (array_key_exists('is_open', $validated)) {
                $availability->is_open = $validated['is_open'];
            }

            $availability->save();
            return;
        }

        // --- CASE 2: Rate row (price only) ---
        if (! is_null($validated['rate_id'])) {
            // Pastikan base row selalu ada
            $baseRow = Availability::firstOrCreate(
                [
                    'unit_id' => $unitId,
                    'date'    => $validated['date'],
                    'rate_id' => null,
                ],
                [
                    'qty'     => null,
                    'is_open' => true,
                ]
            );

            // Rate row → hanya simpan price
            $availability = Availability::firstOrNew([
                'unit_id' => $unitId,
                'date'    => $validated['date'],
                'rate_id' => $validated['rate_id'],
            ]);

            $availability->price = $validated['price'] ?? $availability->price;
            $availability->qty = null;      // enforce null
            $availability->is_open = null;  // enforce null
            $availability->save();

            return;
        }
    }

    public function destroy(Availability $availability) 
    {

    }
}
