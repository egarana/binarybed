<?php

namespace App\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;
use App\LogsModelActivity;

class Permission extends SpatiePermission
{
    use LogsModelActivity;

    protected $fillable = ['name', 'guard_name'];
}
