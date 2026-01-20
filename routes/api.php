<?php

use App\Http\Controllers\Api\ListsController;
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
    // List Sharing & Permissions (most specific first)
    Route::middleware("check.list.role:owner")->group(function () {
        Route::post("lists/{list}/share", [ListsController::class, "share"]);
        Route::patch("lists/{list}/users/{user}", [
            ListsController::class,
            "updateUserRole",
        ]);
        Route::delete("lists/{list}/users/{user}", [
            ListsController::class,
            "removeUser",
        ]);
    });

    // Editors can update lists
    Route::middleware("check.list.role:owner,editor")->group(function () {
        Route::patch("lists/{list}", [ListsController::class, "update"]);
    });

    // Lists resource (less specific)
    Route::apiResource("lists", ListsController::class)->except(["update"]);
    Route::apiResource("tasks", TasksController::class);
    Route::get("lists/{list}", [ListsController::class, "show"]);
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
