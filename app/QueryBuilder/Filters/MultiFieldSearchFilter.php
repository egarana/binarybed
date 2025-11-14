<?php

namespace App\QueryBuilder\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

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
        // Get fields from request, try 'fields' first (cleaner URL), fallback to 'search_fields'
        $request = request();
        $fieldsParam = $request->input('fields') ?? $request->input('search_fields');

        $fields = $fieldsParam
            ? explode(',', $fieldsParam)
            : $this->defaultFields;

        // Get the table name to qualify columns
        $tableName = $query->getModel()->getTable();

        // Build OR condition for each field
        $query->where(function ($q) use ($fields, $value, $tableName) {
            foreach ($fields as $field) {
                $field = trim($field);

                // Handle relation fields (e.g., 'domains.domain')
                if (str_contains($field, '.')) {
                    [$relation, $column] = explode('.', $field, 2);
                    $q->orWhereHas($relation, function ($relationQuery) use ($column, $value) {
                        $relationQuery->where($column, 'like', "%{$value}%");
                    });
                } else {
                    // Handle regular columns with table qualification to avoid ambiguity
                    $q->orWhere("{$tableName}.{$field}", 'like', "%{$value}%");
                }
            }
        });
    }
}