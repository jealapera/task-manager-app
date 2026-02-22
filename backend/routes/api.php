<?php

use App\Http\Controllers\Api\Auth\LoginController;
use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

// Public
Route::post('/login', LoginController::class);

// Authenticated
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', LogoutController::class);

    Route::apiResource('tasks', TaskController::class);
    Route::post('tasks/reorder', [TaskController::class, 'reorder']);
});
