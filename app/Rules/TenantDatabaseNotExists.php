<?php

namespace App\Rules;

use Closure;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Validation\ValidationRule;

class TenantDatabaseNotExists implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Get tenant database configuration
        $prefix = config('tenancy.database.prefix', '');
        $suffix = config('tenancy.database.suffix', '');

        // Construct the database name using the same logic as tenancy
        $databaseName = $prefix . $value . $suffix;

        // Check if database exists
        $exists = $this->databaseExists($databaseName);

        if ($exists) {
            $fail("Database already exists. Contact admin to remove '{$databaseName}'.");
        }
    }

    /**
     * Check if a database exists.
     */
    protected function databaseExists(string $databaseName): bool
    {
        try {
            // Use central connection to check database existence
            $result = DB::connection(config('tenancy.database.central_connection', 'mysql'))
                ->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);

            return count($result) > 0;
        } catch (\Throwable $e) {
            // If we can't check, assume it doesn't exist and let the creation fail naturally
            return false;
        }
    }
}