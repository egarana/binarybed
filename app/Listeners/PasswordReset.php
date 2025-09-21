<?php

namespace App\Listeners;

use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;

class PasswordReset
{
    public function handle(PasswordResetEvent $event): void
    {
        activity()
            ->causedBy($event->user)
            ->performedOn($event->user)
            ->log("User <strong>{$event->user->name}</strong> successfully reset their password.");
    }
}
