<?php

use App\Http\Controllers\ChatController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\TicketController;
use App\Http\Controllers\API\CommentController;
use App\Http\Controllers\API\AttachmentController;
use App\Http\Controllers\API\NotificationController;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
  Route::post('logout', [AuthController::class, 'logout']);

  Route::apiResource('tickets', TicketController::class);
  Route::post('comments', [CommentController::class, 'store']);
  Route::post('attachments', [AttachmentController::class, 'store']);
  Route::post('tickets/{ticket}/assign', [TicketController::class, 'assign']);

  // Admin routes
  Route::prefix('admin')->group(function () {
    Route::get('customers', [AuthController::class, 'getCustomers']);
    Route::get('admins', [AuthController::class, 'getAdmins']);
    Route::get('tickets', [AuthController::class, 'getTickets']);
    Route::get('recent-tickets', [AuthController::class, 'recentTickets']);
    Route::get('tickets/{ticket}', [AuthController::class, 'getTicketDetail']);
    Route::put('tickets/{ticket}', [AuthController::class, 'updateTicket']);
  });


  Route::prefix('tickets/{ticket}/chat')->group(function () {
    Route::get('messages', [ChatController::class, 'getMessages']);
    Route::post('messages', [ChatController::class, 'sendMessage']);
    Route::post('messages/read', [ChatController::class, 'markAsRead']);
    Route::post('upload', [ChatController::class, 'uploadFile']);
  });

  // Notifications
  Route::apiResource('notifications', NotificationController::class)->only(['index', 'destroy']);
  Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead']);
  Route::patch('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
  Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
});
