<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\TaskIndexRequest;
use App\Http\Requests\TaskRequest;
use App\Http\Resources\TaskResource;
use App\Models\Lists;
use App\Models\Tasks;
use Carbon\Carbon;
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
    public function index(TaskIndexRequest $request, Lists $list = null): JsonResponse
    {
        $this->logger->debug("Fetching tasks for list_id={$list->id} at " . now());

        $query = Tasks::query();

        if ($list) {
            $query->where("list_id", $list->id);
        } else {
            // Only tasks user has access to
            $query->whereHas('list.users', fn($q) => $q->where('user_id', auth()->id()));
        }

        // Filters (already validated)
        if ($request->filled("status")) {
            $query->where("status", $request->status);
        }

        if ($request->filled("due") && $request->due === "today") {
            $query->whereDate("due_date", Carbon::today());
        }

        if ($request->boolean("overdue")) {
            $query->whereDate("due_date", "<", Carbon::today())
                ->where("status", "!=", "done");
        }

        $tasks = $query->orderBy("due_date")->get();

        $this->logger->info("Fetched all tasks", ['task_count' => $tasks->count()]);

        return response()->json([
            "status" => "success",
            "message" => "Tasks retrieved successfully",
            "data" => TaskResource::collection($tasks),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Lists $list, TaskRequest $request): JsonResponse
    {
        $this->logger->info("Creating task in list_id={$list->id}", [
            'user_id' => auth()->id(),
            'data' => $request->validated(),
        ]);

        // Use relationship to automatically assign list_id
        $task = $list->tasks()->create(array_merge(
            $request->validated(),
            ['created_by' => auth()->id()]
        ));

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
    public function show(string $listId, string $taskId): JsonResponse
    {
        $task = Tasks::where('id', $taskId)
            ->where('list_id', $listId)
            ->firstOrFail();

        // Optional: double-check user is member of list
        if (!$task->list->users()->where('user_id', auth()->id())->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access to task'
            ], 403);
        }

        return response()->json([
            "status" => "success",
            "data" => TaskResource::make($task),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, Tasks $task): JsonResponse
    {

        $this->logger->debug("Updating task_id={$task->id} with data", [
            'data' => $request->all(),
        ]);

        if (empty($request->all())) {
            return response()->json([
                "status" => "success",
                "message" => "No data change was found",
            ]);
        }

        $data = $request->safe()->only(array_keys($request->rules()));
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


    public function markAsCompleted(string $id): JsonResponse
    {
        $task = Tasks::findOrFail($id);
        $task->status = 'completed';
        $task->completed_at = now();
        $task->save();

        $this->logger->info("Marked task as completed", ['task_id' => $id]);

        return response()->json([
            "status" => "success",
            "message" => "Task marked as completed successfully",
            "data" => TaskResource::make($task),
        ]);
    }

    public function assignedToUser(string $userId): JsonResponse
    {
        $tasks = Tasks::where('assigned_to', $userId)->get();

        $this->logger->info("Fetched tasks assigned to user", ['user_id' => $userId, 'task_count' => $tasks->count()]);

        return response()->json([
            "status" => "success",
            "data" => TaskResource::collection($tasks),
        ]);
    }
}
