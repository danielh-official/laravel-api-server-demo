<?php

use App\Http\Controllers\Api\PartnerController;
use App\Http\Controllers\GetUserController;
use Illuminate\Support\Facades\Route;

Route::name('api.')->middleware('auth:sanctum')->group(function () {
    Route::get('/user', GetUserController::class)->name('user');

    Route::apiResource('partners', PartnerController::class);
});
