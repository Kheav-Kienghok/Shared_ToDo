<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
