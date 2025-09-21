<?php

namespace App\Listeners;

use App\Events\RolePermissionsUpdated;

class LogRolePermissionChanges
{
    public function handle(RolePermissionsUpdated $event): void
    {
        $causer = $event->causer;
        $causerName = $causer?->name ?? 'System';
        $causerEmail = $causer?->email ?? 'system@local';

        if (!empty($event->addedPermissions)) {
            activity()
                ->causedBy($causer)
                ->performedOn($event->role)
                ->withProperties([
                    'permissions' => $event->addedPermissions,
                    'causer' => [
                        'name' => $causerName,
                        'email' => $causerEmail,
                    ],
                ])
                ->log(
                    'Assigned permission(s): <span class="font-semibold">' . implode(', ', $event->addedPermissions) . '</span> to role <span class="font-semibold">' . $event->role->name . '</span> by <span class="font-semibold">' . $causerName . ' (' . $causerEmail . ')</span>.'
                );
        }

        if (!empty($event->removedPermissions)) {
            activity()
                ->causedBy($causer)
                ->performedOn($event->role)
                ->withProperties([
                    'permissions' => $event->removedPermissions,
                    'causer' => [
                        'name' => $causerName,
                        'email' => $causerEmail,
                    ],
                ])
                ->log(
                    'Removed permission(s): <span class="font-semibold">' . implode(', ', $event->removedPermissions) . '</span> from role <span class="font-semibold">' . $event->role->name . '</span> by <span class="font-semibold">' . $causerName . ' (' . $causerEmail . ')</span>.'
                );
        }
    }
}
