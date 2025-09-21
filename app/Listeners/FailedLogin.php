<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Failed;
use Illuminate\Support\Facades\Request;
use App\Models\User;

class FailedLogin
{
    public function handle(Failed $event): void
    {
        $email = $event->credentials['email'] ?? 'unknown';
        $ip = Request::ip();
        $userAgent = Request::userAgent();

        $user = User::where('email', $email)->first();

        $displayName = $user
            ? "{$user->name} ({$email})"
            : $email;

        activity()
            ->withProperties([
                'email' => $email,
                'ip' => $ip,
                'user_agent' => $userAgent,
            ])
            ->log("Failed login attempt for <span class=\"font-semibold\">{$displayName}</span> from IP <code>{$ip}</code>.");
    }
}
