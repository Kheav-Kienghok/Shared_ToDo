<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListRequest;
use App\Http\Resources\ListResource;
use App\Models\Lists;
use App\Models\ListUser;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Facades\DB;

class ListsController extends Controller
{
    protected LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
        $this->authorizeResource(Lists::class, "list");
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = Lists::where("owner_id", auth()->id())
            ->get()
            ->map(function ($list) {
                return new ListResource($list);
            });

        return response()->json([
            "status" => "success",
            "message" => "Lists retrieved successfully",
            "data" => $data,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ListRequest $request)
    {
        $data = $request->validated();
        $userId = auth()->id();
        $data['owner_id'] = $userId;

        $this->logger->info("Creating new list", [
            "user_id" => $userId ?? "unknown",
            "ip" => $request->ip() ?? "unknown",
        ]);

        try {
            $list = DB::transaction(function () use ($data, $userId) {
                // 1. Create the list
                $list = Lists::create($data);

                // 2. Attach the owner to the pivot table
                $list->users()->syncWithoutDetaching([
                    $userId => ['role' => ListUser::ROLE_OWNER]
                ]);

                return $list;
            });

            return response()->json([
                "status" => "success",
                "message" => "List created successfully",
                "data" => new ListResource($list),
            ]);
        } catch (\Throwable $e) {
            $this->logger->error("Failed to create list", [
                "user_id" => $userId,
                "ip" => $request->ip() ?? "unknown",
                "error" => $e->getMessage(),
            ]);

            return response()->json([
                "status" => "error",
                "error" => "Could not create list",
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Lists $list)
    {
        return response()->json([
            "status" => "success",
            "data" => new ListResource($list),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ListRequest $request, Lists $list)
    {
        $this->logger->info("Updating list", [
            "user_id" => auth()->id() ?? "unknown",
            "list_id" => $list->id,
            "ip" => $request->ip() ?? "unknown",
        ]);

        if (empty($request->all())) {
            return response()->json([
                "status" => "success",
                "message" => "No data change was found",
            ]);
        }

        $data = $request->validated();

        $list->update($data);

        return response()->json([
            "status" => "success",
            "message" => "List updated successfully",
            "data" => new ListResource($list),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lists $list)
    {
        $this->logger->info("Deleting list", [
            "user_id" => auth()->id() ?? "unknown",
            "list_id" => $list->id,
            "ip" => request()->ip() ?? "unknown",
        ]);

        $list->delete();

        return response()->json([
            "status" => "success",
            "message" => "List deleted successfully",
        ]);
    }
}
