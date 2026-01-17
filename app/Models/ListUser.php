<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ListUser extends Pivot
{
    protected $table = 'list_users';

    protected $fillable = [
        'role',
        'user_id',
        'list_id',
    ];

    // Role constants
    const ROLE_OWNER = 'owner';
    const ROLE_COLLABORATOR = 'collaborator';
    const ROLE_VIEWER = 'viewer';
}
