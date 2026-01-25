<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tasks extends Model
{
    //
    protected $fillable = [
        'title',
        'description',
        'assigned_to',
        'status',
        'priority',
        'due_date',
        'completed_at',
        'reminder_at',
        'list_id',
        'created_by',
    ];

    protected $casts = [
        'due_date' => 'datetime:Y-m-d H:i:s',
        'completed_at' => 'datetime:Y-m-d H:i:s',
        'reminder_at' => 'datetime:Y-m-d H:i:s',
    ];

    /**
     * The list this task belongs to
     */
    public function list(): BelongsTo
    {
        return $this->belongsTo(Lists::class, 'list_id');
    }

    /**
     * The user this task is assigned to
     */
    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * The user who created this task
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function comments()
    {
        return $this->hasMany(TaskComments::class, 'task_id');
    }

    public function recurrence(): HasOne
    {
        return $this->hasOne(TaskRecurrences::class, 'task_id');
    }
}
