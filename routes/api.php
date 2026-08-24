<?php

use App\Http\Controllers\Api\EgresoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('egresos', EgresoController::class);
});
