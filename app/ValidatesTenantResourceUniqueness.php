<?php

namespace App;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Validator;

trait ValidatesTenantResourceUniqueness
{
    /**
     * Validate field uniqueness within tenant database context.
     *
     * @param Validator $validator
     * @param string $modelClass
     * @param string $tenantId
     * @param string $field
     * @param string $value
     * @param mixed $ignoreValue
     * @return void
     */
    protected function validateTenantUniqueness(
        Validator $validator,
        string $modelClass,
        string $tenantId,
        string $field,
        string $value,
        mixed $ignoreValue = null
    ): void {
        if ($validator->errors()->has($field)) {
            return;
        }

        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return;
        }

        tenancy()->initialize($tenant);

        try {
            /** @var Model $model */
            $query = $modelClass::where($field, $value);

            if ($ignoreValue !== null) {
                $query->where($field, '!=', $ignoreValue);
            }

            if ($query->exists()) {
                $validator->errors()->add(
                    $field,
                    "This {$field} is already in use. Please choose another one."
                );
            }
        } finally {
            tenancy()->end();
        }
    }
}