<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\AttachmentController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::apiResource('tickets', TicketController::class);
    Route::post('comments', [CommentController::class, 'store']);
    Route::post('attachments', [AttachmentController::class, 'store']);
});
