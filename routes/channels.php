<?php

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Channel for ticket chat - using public channel for simplicity
Broadcast::channel('ticket.{ticketId}', function (User $user, int $ticketId) {

  return ['id' => $user->id, 'name' => $user->name, 'role' => $user->role];
});

// Admin notifications channel - all admins
Broadcast::channel('admin-notifications', function (User $user) {
  return $user->role === 'admin';
});

// User-specific notifications
Broadcast::channel('user.{userId}', function (User $user, int $userId) {
  return $user->id === $userId;
});

// Ticket chat channel (existing)
Broadcast::channel('ticket.{ticketId}', function (User $user, int $ticketId) {
  return ['id' => $user->id, 'name' => $user->name, 'role' => $user->role];
});
