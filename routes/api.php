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

    Route::apiResource("lists", ListsController::class);

    Route::apiResource("tasks", TasksController::class);
});



/*
|--------------------------------------------------------------------------
| Fallback (404 JSON)
|--------------------------------------------------------------------------
*/
Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Route not found'
    ], 404);
});
