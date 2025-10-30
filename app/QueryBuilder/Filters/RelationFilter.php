<?php

namespace App\QueryBuilder\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

/**
 * Reusable filter untuk relasi.
 * Contoh: new RelationFilter('domains', 'domain')
 */
class RelationFilter implements Filter
{
    protected string $relation;
    protected string $column;

    public function __construct(string $relation, string $column)
    {
        $this->relation = $relation;
        $this->column   = $column;
    }

    public function __invoke(Builder $query, $value, string $property)
    {
        $query->whereHas($this->relation, function ($q) use ($value) {
            $q->where($this->column, 'like', "%{$value}%");
        });
    }
}
