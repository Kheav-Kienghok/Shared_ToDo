<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\HabitStreaks;
use App\Models\Tasks;
use Illuminate\Http\Request;

class HabitsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $habits = Tasks::where('type', 'habit')
            ->with(['streaks', 'recurrences', 'completions'])
            ->get();
        return response()->json($habits);

    }

    public function addRecurrence(Request $request, Tasks $task)
    {
        $request->validate([
            'type' => 'required|string',
            'frequency' => 'required|string',
            'interval' => 'required|integer',
        ]);

        $task->recurrences()->create([
            'type' => $request->type,
            'frequency' => $request->frequency,
            'interval' => $request->interval,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Recurrence added successfully',
            'data' => $task->recurrences,
        ]);
    }

    public function addCompletion(Request $request, Tasks $task)
    {

        $streak = $task->habitStreak;

        if (!$streak) {
            $streak = HabitStreaks::create([
                'task_id' => $task->id,
                'current_streak' => 1,
                'longest_streak' => 1,
            ]);
        } else {
            $streak->current_streak += 1;

            if ($streak->current_streak > $streak->longest_streak) {
                $streak->longest_streak = $streak->current_streak;
            }

            $streak->save(); // boot() will set last_completed_at automatically
        }

        return response()->json($streak);
    }
}
