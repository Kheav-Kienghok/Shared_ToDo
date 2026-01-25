<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HabitStreaks extends Model
{

    protected $fillable = [
        'current_streak',
        'longest_streak',
        'task_id',
    ];

    public $timestamps = false;

    public function casts()
    {
        return [
            'current_streak' => 'integer',
            'longest_streak' => 'integer',
            'last_completed_at' => 'datetime:Y-m-d H:i:s',
        ];
    }

    protected static function booted()
    {
        static::saving(function ($streak) {
            if ($streak->isDirty('current_streak')) {

                $original = $streak->getOriginal('current_streak');

                // Only when streak increases (actual completion)
                if ($streak->current_streak > $original) {
                    $streak->last_completed_at = now();
                }
            }
        });
    }

    public function task()
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }
}
