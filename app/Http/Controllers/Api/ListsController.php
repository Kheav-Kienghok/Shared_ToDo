<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListRequest;
use App\Http\Resources\ListResource;
use App\Models\Lists;
use Psr\Log\LoggerInterface;

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
        $data["owner_id"] = auth()->id();

        $this->logger->info("Creating new list", [
            "user_id" => auth()->id() ?? "unknown",
            "ip" => $request->ip() ?? "unknown",
        ]);

        $list = Lists::create($data);

        return response()->json([
            "status" => "success",
            "message" => "List created successfully",
            "data" => new ListResource($list),
        ]);
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

        $list->delete();

        return response()->json([
            "status" => "success",
            "message" => "List deleted successfully",
        ]);
    }
}
