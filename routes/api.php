<?php

use App\Http\Controllers\Api\HabitsController;
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

    Route::prefix("lists")->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Lists Core
        |--------------------------------------------------------------------------
        */
        Route::apiResource("/", ListsController::class)->except(["update", "show"]);

        Route::get("{list}", [ListsController::class, "show"]);

        // Editors + Owners can update list
        Route::middleware("check.list.role:owner,editor")->group(function () {
            Route::patch("{list}", [ListsController::class, "update"]);
        });


        /*
        |--------------------------------------------------------------------------
        | List Sharing & Permissions (Owner only)
        |--------------------------------------------------------------------------
        */
        Route::middleware("check.list.role:owner")->group(function () {

            Route::post("{list}/share", [ListsController::class, "share"]);
            Route::patch("{list}/users", [ListsController::class, "updateUserRole"]);
            Route::delete("{list}/users", [ListsController::class, "removeUser"]);
        });


        /*
        |--------------------------------------------------------------------------
        | Tasks under Lists
        |--------------------------------------------------------------------------
        */
        Route::middleware("check.list.role:owner,editor,viewer")->group(function () {

            // List + filter tasks
            Route::get("{list}/tasks", [TasksController::class, "index"]);

            // Show single task inside list
            Route::get("{list}/tasks/{task}", [TasksController::class, "show"]);

            // Create task inside list
            Route::post("{list}/tasks", [TasksController::class, "store"]);
        });

    });


    Route::prefix("tasks")->group(function () {

        Route::patch("{task}", [TasksController::class, "update"]);
        Route::delete("{task}", [TasksController::class, "destroy"]);

        Route::post("{task}/complete", [TasksController::class, "markAsCompleted"]);
        Route::post("{task}/assign", [TasksController::class, "assignedToUser"]);

        /*
        |--------------------------------------------------------------------------
        | Task Comments
        |--------------------------------------------------------------------------
        */
        Route::get("{task}/comments", [TaskCommentsController::class, "index"]);
        Route::post("{task}/comments", [TaskCommentsController::class, "store"]);


        /*
        |--------------------------------------------------------------------------
        | Task Recurrences & Completions
        |--------------------------------------------------------------------------
        */
        Route::post("{task}/reccurrences", [HabitsController::class, "addRecurrence"]);
        Route::post("{task}/completions", [HabitsController::class, "addCompletion"]);
    });

    Route::prefix("comments")->group(function () {
        Route::delete("{comment}", [TaskCommentsController::class, "destroy"]);
    });


    Route::prefix("habits")->group(function () {
        Route::get("streaks", [HabitsController::class, "index"]);
    });

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
