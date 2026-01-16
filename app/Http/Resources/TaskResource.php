<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'assigned_to' => $this->assigned_to,
            'status' => $this->status,
            'priority' => $this->priority,
            'completed_at' => $this->completed_at ? $this->completed_at->format('Y-m-d H:i:s') : null,
            'reminder_at' => $this->reminder_at ? $this->reminder_at->format('Y-m-d H:i:s') : null,
            'due_date' => $this->due_date ? $this->due_date->format('Y-m-d H:i:s') : null,
        ];
    }
}
