<?php

namespace App\Models;

use Spatie\Permission\Models\Role as SpatieRole;
use App\LogsModelActivity;

class Role extends SpatieRole
{
    use LogsModelActivity;

    protected $fillable = ['name', 'guard_name'];
}
