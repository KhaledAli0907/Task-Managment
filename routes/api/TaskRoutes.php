<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('task')->middleware('auth:api')->group(function () {
    // All routes use auth:api, permission middleware is handled in controller constructor
    Route::post('/', [TaskController::class, 'store']);
    Route::get('/', [TaskController::class, 'index']);
    Route::get('/{id}', [TaskController::class, 'show']);
    Route::put('/{id}', [TaskController::class, 'update']);
    Route::delete('/{id}', [TaskController::class, 'destroy']);
    Route::post('/{id}/assign', [TaskController::class, 'assign']);
    Route::patch('/{id}/status', [TaskController::class, 'changeTaskStatus']);
    Route::post('/{id}/dependencies', [TaskController::class, 'addChild']);
    Route::delete('/dependencies/{childId}', [TaskController::class, 'removeChild']);
});
