<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Tasks;
use Illuminate\Http\JsonResponse;
use Psr\Log\LoggerInterface;

class TasksController extends Controller
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $this->logger->debug("Debugging info: Fetching tasks at " . now());

        $tasks = Tasks::all();

        $this->logger->info("Fetched all tasks", ['task_count' => $tasks->count()]);

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
        $task = Tasks::create($request->validated());

        $this->logger->info("Created new task", ['task_id' => $task->id]);

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
        $task = Tasks::findOrFail($id);

        return response()->json([
            "status" => "success",
            "data" => TaskResource::make($task),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, string $id): JsonResponse
    {
        if (empty($request->all())) {
            return response()->json([
                "status" => "success",
                "message" => "No data change was found",
            ]);
        }

        $data = $request->safe()->only(array_keys($request->rules()));
        $task = Tasks::findOrFail($id);

        $task->update($data);

        $this->logger->info("Updated task", ['task_id' => $task->id]);

        return response()->json([
            "status" => "success",
            "message" => "Task updated successfully",
            "data" => TaskResource::make($task),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $task = Tasks::findOrFail($id);
        $task->delete();

        $this->logger->info("Deleted task", ['task_id' => $id]);

        return response()->json([
            "status" => "success",
            "message" => "Task deleted successfully",
        ]);
    }
}
