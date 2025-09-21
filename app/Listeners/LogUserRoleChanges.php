<?php

namespace App\Listeners;

use App\Events\UserRolesUpdated;

class LogUserRoleChanges
{
    public function handle(UserRolesUpdated $event): void
    {
        $causer = $event->causer;
        $causerName = $causer?->name ?? 'System';
        $causerEmail = $causer?->email ?? 'system@local';

        if (!empty($event->addedRoles)) {
            activity()
                ->causedBy($causer)
                ->performedOn($event->user)
                ->withProperties([
                    'roles' => $event->addedRoles,
                    'causer' => [
                        'name' => $causerName,
                        'email' => $causerEmail,
                    ],
                ])
                ->log(
                    'Assigned role(s): <span class="font-semibold">' . implode(', ', $event->addedRoles) . '</span> to user <span class="font-semibold">' . $event->user->name . '</span> by <span class="font-semibold">' . $causerName . ' (' . $causerEmail . ')</span>.'
                );
        }

        if (!empty($event->removedRoles)) {
            activity()
                ->causedBy($causer)
                ->performedOn($event->user)
                ->withProperties([
                    'roles' => $event->removedRoles,
                    'causer' => [
                        'name' => $causerName,
                        'email' => $causerEmail,
                    ],
                ])
                ->log(
                    'Removed role(s): <span class="font-semibold">' . implode(', ', $event->removedRoles) . '</span> from user <span class="font-semibold">' . $event->user->name . '</span> by <span class="font-semibold">' . $causerName . ' (' . $causerEmail . ')</span>.'
                );
        }
    }
}
