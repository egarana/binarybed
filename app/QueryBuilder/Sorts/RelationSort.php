<?php

namespace App\QueryBuilder\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Spatie\QueryBuilder\Sorts\Sort;

/**
 * Reusable sorter untuk relasi "hasOne" atau "hasMany".
 * Menyortir berdasarkan nilai MIN atau MAX dari kolom relasi.
 */
class RelationSort implements Sort
{
    protected string $relation;
    protected string $column;
    protected string $aggregate;

    /**
     * @param string $relation  Nama relasi (contoh: 'domains')
     * @param string $column    Nama kolom relasi (contoh: 'domain')
     * @param string $aggregate Fungsi agregasi: 'MIN' atau 'MAX'
     */
    public function __construct(string $relation, string $column, string $aggregate = 'MIN')
    {
        $this->relation = $relation;
        $this->column   = $column;
        $this->aggregate = strtoupper($aggregate);
    }

    public function __invoke(Builder $query, bool $descending, string $property)
    {
        $direction = $descending ? 'desc' : 'asc';
        $mainTable = $query->getModel()->getTable();
        $relationTable = $this->relation;

        // Gunakan fungsi agregat agar valid di ONLY_FULL_GROUP_BY mode
        $query->leftJoin("{$relationTable}", "{$relationTable}.tenant_id", '=', "{$mainTable}.id")
              ->select("{$mainTable}.*")
              ->selectRaw("{$this->aggregate}({$relationTable}.{$this->column}) as sort_column")
              ->groupBy("{$mainTable}.id")
              ->orderBy('sort_column', $direction);
    }
}
