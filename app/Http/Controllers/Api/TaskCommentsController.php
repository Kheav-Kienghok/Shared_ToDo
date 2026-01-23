<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TaskCommentResource;
use App\Models\TaskComments;
use App\Models\Tasks;
use Illuminate\Http\Request;
use Psr\Log\LoggerInterface;

class TaskCommentsController extends Controller
{
    protected LoggerInterface $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(string $task)
    {
        //
        $task = Tasks::findOrFail($task);

        $comments = $task->comments()
            ->with('user:id,name')
            ->latest()
            ->get();

        return response()->json([
            'status' => 'success',
            'message' => 'Comments fetched successfully',
            'data' => TaskCommentResource::collection($comments),
        ]);

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $task)
    {

        $this->logger->info("Adding comment to task", ["task_id" => $task, "user_id" => auth()->id()]);

        $request->validate([
            'comment' => 'required|string',
        ]);

        $task = Tasks::findOrFail($task);

        $comment = $task->comments()->create([
            'comment' => $request->comment,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'status' => 'success',
            "message" => "Comment added successfully",
            'data' => $comment,
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $comment)
    {
        $comment = TaskComments::findOrFail($comment);

        if ($comment->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized to delete this comment',
            ], 403);
        }

        $comment->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Comment deleted successfully',
        ]);

    }
}
