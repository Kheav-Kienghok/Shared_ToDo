<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class TasksController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        Log::info("Fetching all tasks", [
            "user_id" => auth()->id(),
            "ip" => request()->ip(),
        ]);

        $tasks = Task::all();

        return response()->json([
            "status" => "success",
            "data" => TaskResource::collection($tasks),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request): JsonResponse
    {
        $task = Task::create($request->validated());

        return response()->json(
            [
                "status" => "success",
                "message" => "Task created successfully",
                "data" => TaskResource::make($task),
            ],
            201,
        );
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $task = Task::findOrFail($id);

        return response()->json([
            "status" => "success",
            "data" => TaskResource::make($task),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, string $id)
    {
        $data = $request->validated();

        // If no fields were provided, do nothing
        if (empty($data)) {
            return response()->json([
                "status" => "success",
                "message" => "No changes made to the task",
            ]);
        }

        $task = Task::findOrFail($id);

        $task->update($data);

        return response()->json([
            "status" => "success",
            "message" => "Task updated successfully",
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $task = Task::findOrFail($id);
        $task->delete();

        return response()->json([
            "status" => "success",
            "message" => "Task deleted successfully",
        ]);
    }
}
