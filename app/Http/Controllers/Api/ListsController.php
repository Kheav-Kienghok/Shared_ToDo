<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ListRequest;
use App\Http\Requests\ShareListRequest;
use App\Http\Requests\UpdateUserRoleRequest;
use App\Http\Resources\ListResource;
use App\Models\Lists;
use App\Models\ListUser;
use App\Models\User;
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

    /**
     * Share a list with another user (assign role: collaborator/viewer)
     */
    public function share(ShareListRequest $request, Lists $list)
    {
        $this->authorize('share', $list);

        $userId = $request->input('user_id');
        $role = $request->input('role');

        $user = User::findOrFail($userId);

        $result = $this->attachOrUpdateUserRole($list, $user, $role);

        if ($result['action'] === 'owner') {
            return response()->json([
                'status' => 'success',
                'message' => "User {$result['user_name']} is the owner and their role cannot be changed",
                'data' => $result,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => "User {$result['user_name']} {$result['action']} as {$result['role']}",
            'data' => $result,
        ]);
    }

    /**
     * Update a user's role on a list
     */
    public function updateUserRole(UpdateUserRoleRequest $request, Lists $list, User $user)
    {
        $this->authorize('share', $list);

        $role = $request->input('role');

        $result = $this->attachOrUpdateUserRole($list, $user, $role);

        if ($result['action'] === 'owner') {
            return response()->json([
                'status' => 'success',
                'message' => "User {$result['user_name']} is the owner and their role cannot be changed",
                'data' => $result,
            ]);
        }

        $status = $result['action'] === 'added' ? 'warning' : 'success';
        $message = $result['action'] === 'added'
            ? "User {$result['user_name']} was not on the list and has been added as {$role}"
            : "User {$result['user_name']}'s role updated to {$role}";

        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $result,
        ]);
    }

    /**
     * Remove a user from a list
     */
    public function removeUser(Lists $list, User $user)
    {
        // Policy check: only owner can remove users
        $this->authorize('share', $list);

        // Check if user is attached to the list
        $pivot = $list->users()->where('user_id', $user->id)->first();

        if (!$pivot) {
            return response()->json([
                'status' => 'error',
                'message' => "User {$user->name} is not part of this list",
            ], 404);
        }

        // Optional: get role before removing
        $role = $pivot->pivot->role;

        // Remove user
        $list->users()->detach($user->id);

        return response()->json([
            'status' => 'success',
            'message' => "User {$user->name} with role '{$role}' removed from the list",
        ]);
    }


    /**
     * Attach or update a user on a list with a specific role
     */
    protected function attachOrUpdateUserRole(Lists $list, User $user, string $role): array
    {
        $pivot = $list->users()->where('user_id', $user->id)->first();

        // Prevent changing owner's role
        if ($pivot && $pivot->pivot->role === ListUser::ROLE_OWNER) {
            return [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'role' => ListUser::ROLE_OWNER,
                'action' => 'owner', // special action
            ];
        }

        if ($pivot) {
            $list->users()->updateExistingPivot($user->id, ['role' => $role]);
            $action = 'updated';
        } else {
            $list->users()->syncWithoutDetaching([$user->id => ['role' => $role]]);
            $action = 'added';
        }

        return [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'role' => $role,
            'action' => $action,
        ];
    }

}
