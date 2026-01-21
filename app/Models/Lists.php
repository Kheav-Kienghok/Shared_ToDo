<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lists extends Model
{

    protected $fillable = [
        'name',
        'description',
        'is_achieved',
        'owner_id',
    ];

    protected $casts = [
        'is_achieved' => 'boolean',
    ];

    // Owner of the list
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    // Users associated with this list (pivot: list_users)
    public function users(): BelongsToMany
    {

        return $this->belongsToMany(
            User::class,
            'list_users',
            'list_id', // ✅ correct pivot FK
            'user_id'  // ✅ correct pivot FK
        )
            ->using(ListUser::class)
            ->withPivot('role')
            ->withTimestamps();
    }

    /**
     * Tasks that belong to this list
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Tasks::class, 'list_id');
    }
}
