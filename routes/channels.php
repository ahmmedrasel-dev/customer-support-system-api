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
  \Log::info('🔐 Public channel authorization', [
    'user_id' => $user->id,
    'user_role' => $user->role,
    'ticket_id' => $ticketId,
    'user_name' => $user->name
  ]);

  // For public channels, just return user data for presence
  return ['id' => $user->id, 'name' => $user->name, 'role' => $user->role];
});
