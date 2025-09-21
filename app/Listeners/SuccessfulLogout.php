<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;

class SuccessfulLogout
{
    public function handle(Logout $event): void
    {
        activity()
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->log("User <span class=\"font-semibold\">{$event->user->name}</span> logged out.");
    }
}
