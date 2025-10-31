<?php

namespace App\QueryBuilder\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Filter untuk search di multiple fields dengan OR condition.
 *
 * Usage:
 * AllowedFilter::custom('search', new MultiFieldSearchFilter())
 *
 * Query params:
 * ?filter[search]=keyword&search_fields=name,email,id
 */
class MultiFieldSearchFilter implements Filter
{
    protected array $defaultFields;

    public function __construct(array $defaultFields = ['name'])
    {
        $this->defaultFields = $defaultFields;
    }

    public function __invoke(Builder $query, $value, string $property)
    {
        // Get search_fields from request, fallback to default
        $request = request();
        $fieldsParam = $request->input('search_fields');

        $fields = $fieldsParam
            ? explode(',', $fieldsParam)
            : $this->defaultFields;

        // Build OR condition for each field
        $query->where(function ($q) use ($fields, $value) {
            foreach ($fields as $field) {
                $field = trim($field);

                // Handle relation fields (e.g., 'domains.domain')
                if (str_contains($field, '.')) {
                    [$relation, $column] = explode('.', $field, 2);
                    $q->orWhereHas($relation, function ($relationQuery) use ($column, $value) {
                        $relationQuery->where($column, 'like', "%{$value}%");
                    });
                } else {
                    // Handle regular columns
                    $q->orWhere($field, 'like', "%{$value}%");
                }
            }
        });
    }
}