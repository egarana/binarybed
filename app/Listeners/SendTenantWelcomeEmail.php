<?php

namespace App\Listeners;

use App\Events\TenantCreated;
use App\Jobs\SendTenantWelcomeJob;

class SendTenantWelcomeEmail
{
    /**
     * Tangani event.
     */
    public function handle(TenantCreated $event): void
    {
        // bisa langsung dispatch job
        SendTenantWelcomeJob::dispatch($event->tenant);
    }
}
