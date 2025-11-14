<?php

namespace App\Services;

use Illuminate\Http\Request;

class PaginationService
{
    /**
     * Valid per-page pagination options.
     *
     * @var int[]
     */
    protected array $allowedPerPageOptions = [10, 15, 20, 30, 40, 50];

    /**
     * Resolve per-page value from request safely.
     *
     * @param  Request  $request
     * @param  int      $default
     * @param  int      $max
     * @return int
     */
    public function resolvePerPage(Request $request, int $default = 15, int $max = 50): int
    {
        $perPage = $request->integer('per_page', $default);

        // pastikan valid integer > 0
        if ($perPage < 1) {
            return $default;
        }

        // hard cap untuk keamanan (anti abuse)
        $perPage = min($perPage, $max);

        // snap ke opsi *terbesar* yang <= input
        $snapped = collect($this->allowedPerPageOptions)
            ->filter(fn ($v) => $v <= $perPage)
            ->last();

        // jika input kurang dari minimum $allowedPerPageOptions, fallback default
        return $snapped ?? $default;
    }
}
