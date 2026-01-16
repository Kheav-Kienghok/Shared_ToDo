<?php

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

Route::middleware("jwt")->group(function () {
    Route::apiResource("tasks", TasksController::class);
});
