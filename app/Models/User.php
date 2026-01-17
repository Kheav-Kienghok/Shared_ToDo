<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = ["name", "email", "password"];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = ["password"];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            "password" => "hashed",
        ];
    }

    // JWT REQUIRED METHODS
    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [];
    }

    // Relationships

    // Lists owned by the user
    public function listsOwned(): HasMany
    {
        return $this->hasMany(Lists::class, 'owner_id');
    }

    // Lists where the user is a collaborator or viewer
    public function lists(): BelongsToMany
    {
        return $this->belongsToMany(
            Lists::class,
            'list_users',
            'user_id', // ✅ correct
            'list_id'  // ✅ correct
        )
            ->using(ListUser::class)
            ->withPivot('role')
            ->withTimestamps();
    }
}
