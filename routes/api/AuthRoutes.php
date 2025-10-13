<?php

use App\Http\Controllers\Api\AuthController;


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:60,1');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:api');
});
