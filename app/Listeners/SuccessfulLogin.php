<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class SuccessfulLogin
{
    public function handle(Login $event): void
    {
        activity()
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->log("User <span class=\"font-semibold\">{$event->user->name}</span> logged in.");
    }
}
