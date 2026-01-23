<?php

use App\Http\Controllers\Api\ListsController;
use App\Http\Controllers\Api\TaskCommentsController;
use App\Http\Controllers\Api\TasksController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::get("/health", function () {
    return response()->json(["status" => "ok"], 200);
});

Route::prefix("auth")->group(function () {
    Route::post("register", [AuthController::class, "register"]);
    Route::post("login", [AuthController::class, "login"]);

    Route::middleware("jwt")->group(function () {
        Route::post("logout", [AuthController::class, "logout"]);
        Route::post("profile", [AuthController::class, "profile"]);
    });
});

/**
 * @Scramble\SecurityScheme(name="jwt")
 */
Route::middleware("jwt")->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Lists Core
    |--------------------------------------------------------------------------
    */
    Route::apiResource("lists", ListsController::class)->except(["update", "show"]);

    Route::get("lists/{list}", [ListsController::class, "show"]);

    // Editors + Owners can update list
    Route::middleware("check.list.role:owner,editor")->group(function () {
        Route::patch("lists/{list}", [ListsController::class, "update"]);
    });


    /*
    |--------------------------------------------------------------------------
    | List Sharing & Permissions (Owner only)
    |--------------------------------------------------------------------------
    */
    Route::middleware("check.list.role:owner")->group(function () {

        // Share list with new user
        Route::post("lists/{list}/share", [ListsController::class, "share"]);

        // Update user role in list (user_id in body)
        Route::patch("lists/{list}/users", [ListsController::class, "updateUserRole"]);

        // Remove user from list (user_id in body)
        Route::delete("lists/{list}/users", [ListsController::class, "removeUser"]);
    });


    /*
    |--------------------------------------------------------------------------
    | Tasks under Lists
    |--------------------------------------------------------------------------
    */
    Route::middleware("check.list.role:owner,editor,viewer")->group(function () {

        // List + filter tasks
        Route::get("lists/{list}/tasks", [TasksController::class, "index"]);

        // Show single task (important — prevents cross-list leaks)
        Route::get("lists/{list}/tasks/{task}", [TasksController::class, "show"]);

        // Create task
        Route::post("lists/{list}/tasks", [TasksController::class, "store"]);
    });


    /*
    |--------------------------------------------------------------------------
    | Tasks Direct Actions
    |--------------------------------------------------------------------------
    */
    Route::patch("tasks/{task}", [TasksController::class, "update"]);
    Route::delete("tasks/{task}", [TasksController::class, "destroy"]);

    Route::post("tasks/{task}/complete", [TasksController::class, "markAsCompleted"]);
    Route::post("tasks/{task}/assign", [TasksController::class, "assignedToUser"]);


    /*
    |--------------------------------------------------------------------------
    | Task Comments
    |--------------------------------------------------------------------------
    */
    Route::get("tasks/{task}/comments", [TaskCommentsController::class, "index"]);
    Route::post("tasks/{task}/comments", [TaskCommentsController::class, "store"]);
    Route::delete("comments/{comment}", [TaskCommentsController::class, "destroy"]);
});


/*
|--------------------------------------------------------------------------
| Fallback (404 JSON)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->json(
        [
            "status" => "error",
            "message" => "Route not found",
        ],
        404,
    );
});
