<?php

namespace App\Services;

use App\Models\Unit;
use App\Models\User;
use App\Models\UserTenant;
use Illuminate\Support\Facades\DB;

/**
 * Service untuk menangani sinkronisasi User dari central ke tenant database
 * dan attachment ke resources (Unit, dll)
 */
class UserSyncService
{
    /**
     * Sync user from central to tenant database
     *
     * @param string $centralUserId - global_id dari central database (UUID string)
     * @return UserTenant
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public static function syncToTenant(string $centralUserId): UserTenant
    {
        // Get user from central database
        $centralUser = User::on('central')->where('global_id', $centralUserId)->firstOrFail();

        // Check if user already exists in tenant
        $tenantUser = UserTenant::where('global_id', $centralUserId)->first();

        if ($tenantUser) {
            // Update existing user dengan data terbaru dari central
            $tenantUser->update([
                'name' => $centralUser->name,
                'email' => $centralUser->email,
            ]);
        } else {
            // Create new user in tenant
            $tenantUser = UserTenant::create([
                'global_id' => $centralUser->global_id,
                'name' => $centralUser->name,
                'email' => $centralUser->email,
            ]);
        }

        return $tenantUser;
    }

    /**
     * Sync multiple users from central to tenant database
     *
     * @param array $centralUserIds - Array of IDs dari central database
     * @return \Illuminate\Support\Collection
     */
    public static function syncMultipleToTenant(array $centralUserIds)
    {
        $tenantUsers = collect();

        foreach ($centralUserIds as $userId) {
            $tenantUsers->push(self::syncToTenant($userId));
        }

        return $tenantUsers;
    }

    /**
     * Attach unit to user dengan auto-sync
     *
     * @param string $centralUserId - global_id dari central database (UUID string)
     * @param Unit $unit
     * @param array $pivotData - Additional pivot data
     * @return void
     */
    public static function attachUnitToUser(string $centralUserId, Unit $unit, array $pivotData = []): void
    {
        // Sync user dulu dari central ke tenant
        $tenantUser = self::syncToTenant($centralUserId);

        // Default pivot data
        $defaultPivotData = [
            'assigned_at' => now(),
        ];

        // Merge dengan pivot data yang diberikan
        $pivotData = array_merge($defaultPivotData, $pivotData);

        // Attach unit ke user (syncWithoutDetaching agar tidak hapus yang sudah ada)
        $unit->users()->syncWithoutDetaching([
            $tenantUser->global_id => $pivotData
        ]);
    }

    /**
     * Attach multiple units to user dengan auto-sync
     *
     * @param string $centralUserId - global_id dari central database (UUID string)
     * @param array $unitIds - Array of unit IDs
     * @param array $pivotData - Additional pivot data (akan digunakan untuk semua units)
     * @return void
     */
    public static function attachUnitsToUser(string $centralUserId, array $unitIds, array $pivotData = []): void
    {
        // Sync user dulu
        $tenantUser = self::syncToTenant($centralUserId);

        // Default pivot data
        $defaultPivotData = [
            'assigned_at' => now(),
        ];

        $pivotData = array_merge($defaultPivotData, $pivotData);

        // Get all units
        $units = Unit::whereIn('id', $unitIds)->get();

        // Attach semua units ke user
        foreach ($units as $unit) {
            $unit->users()->syncWithoutDetaching([
                $tenantUser->global_id => $pivotData
            ]);
        }
    }

    /**
     * Detach unit from user
     *
     * @param string $centralUserId - global_id dari central database (UUID string)
     * @param Unit $unit
     * @return void
     */
    public static function detachUnitFromUser(string $centralUserId, Unit $unit): void
    {
        // Cari user di tenant
        $tenantUser = UserTenant::where('global_id', $centralUserId)->first();

        if ($tenantUser) {
            $unit->users()->detach($tenantUser->global_id);
        }
    }

    /**
     * Update user's role for a specific unit
     *
     * @param string $centralUserId - global_id dari central database (UUID string)
     * @param Unit $unit
     * @param string $role - Role to set (partner/referrer)
     * @return void
     */
    public static function updateUnitUserRole(string $centralUserId, Unit $unit, string $role): void
    {
        // Cari user di tenant
        $tenantUser = UserTenant::where('global_id', $centralUserId)->firstOrFail();

        // Update pivot data (role kolom di resource_users table)
        $unit->users()->updateExistingPivot($tenantUser->global_id, [
            'role' => $role,
        ]);
    }


    /**
     * Sync assignment: hapus semua assignment lama dan buat yang baru
     *
     * @param string $centralUserId - global_id dari central database (UUID string)
     * @param array $unitIds
     * @param array $pivotData
     * @return void
     */
    public static function syncUnitsForUser(string $centralUserId, array $unitIds, array $pivotData = []): void
    {
        // Sync user dulu
        $tenantUser = self::syncToTenant($centralUserId);

        // Default pivot data
        $defaultPivotData = [
            'assigned_at' => now(),
        ];

        $pivotData = array_merge($defaultPivotData, $pivotData);

        // Prepare sync data
        $syncData = [];
        foreach ($unitIds as $unitId) {
            $syncData[$unitId] = $pivotData;
        }

        // Sync akan otomatis:
        // 1. Detach units yang tidak ada di array
        // 2. Attach units yang baru
        // 3. Update pivot data untuk yang sudah ada
        DB::table('resource_users')
            ->where('global_id', $tenantUser->global_id)
            ->where('resourceable_type', Unit::class)
            ->delete();

        // Kemudian attach semua yang baru
        $units = Unit::whereIn('id', $unitIds)->get();
        foreach ($units as $unit) {
            $unit->users()->attach($tenantUser->global_id, $pivotData);
        }
    }
}
