<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PanelStateController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'panel');

Route::prefix('app')->group(function () {
    Route::get('/session', [AuthController::class, 'session']);
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/recover', [AuthController::class, 'recover'])->middleware('throttle:5,1');
    Route::post('/reset-password', [AuthController::class, 'reset']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::middleware('auth')->group(function () {
        Route::get('/state', [PanelStateController::class, 'show']);
        Route::put('/state', [PanelStateController::class, 'update']);
    });
});
