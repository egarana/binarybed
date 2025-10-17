<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TenantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'      => $this->id,
            'name'    => $this->name,
            'slug'    => $this->slug,
            'domain'  => optional($this->domains()->first())->domain,
            'created' => $this->created_at?->toDateTimeString(),
        ];
    }
}
