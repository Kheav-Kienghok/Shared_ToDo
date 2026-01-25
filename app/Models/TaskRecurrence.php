<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskRecurrence extends Model
{
    protected $table = 'task_recurrences';

    protected $fillable = [
        'frequency',
        'interval',
        'task_id',
    ];

    public $timestamps = false;

    protected static function booted()
    {
        static::creating(function ($model) {
            if (is_null($model->last_generated_at)) {
                $model->last_generated_at = now();
            }
        });
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Tasks::class, 'task_id');
    }
}
