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
