<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\AttachmentController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
  Route::post('logout', [AuthController::class, 'logout']);

  Route::apiResource('tickets', TicketController::class);
  Route::post('comments', [CommentController::class, 'store']);
  Route::post('attachments', [AttachmentController::class, 'store']);

  // Admin routes
  Route::prefix('admin')->group(function () {
    Route::get('customers', [AuthController::class, 'getCustomers']);
    Route::get('tickets', [AuthController::class, 'getTickets']);
    Route::get('tickets/{ticket}', [AuthController::class, 'getTicketDetail']);
    Route::put('tickets/{ticket}', [AuthController::class, 'updateTicket']);
  });


  Route::prefix('tickets/{ticket}/chat')->group(function () {
    Route::get('messages', [ChatController::class, 'getMessages']);
    Route::post('messages', [ChatController::class, 'sendMessage']);
    Route::post('messages/read', [ChatController::class, 'markAsRead']);
    Route::post('upload', [ChatController::class, 'uploadFile']);
  });
});
