<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReturnRequestController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/returns', [ReturnRequestController::class, 'store']);
    Route::get('/returns/{id}', [ReturnRequestController::class, 'show']);
    Route::patch('/returns/{id}/status', [ReturnRequestController::class, 'updateStatus']);
});
