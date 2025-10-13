<?php

use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('task')->group(function () {
    Route::post('/', [TaskController::class, 'store'])->middleware('auth:api');
    Route::get('/', [TaskController::class, 'index'])->middleware('auth:api');
    Route::get('/{id}', [TaskController::class, 'show'])->middleware('auth:api');
    Route::put('/{id}', [TaskController::class, 'update'])->middleware('auth:api');
    Route::delete('/{id}', [TaskController::class, 'destroy'])->middleware('auth:api');
    Route::post('/{id}/assign', [TaskController::class, 'assign'])->middleware('auth:api');
    Route::patch('/{id}/status', [TaskController::class, 'changeTaskStatus'])->middleware('auth:api');
    Route::post('/{id}/dependencies', [TaskController::class, 'addDependency'])->middleware('auth:api');
    Route::delete('/{id}/dependencies/{dependencyId}', [TaskController::class, 'removeDependency'])->middleware('auth:api');
});
