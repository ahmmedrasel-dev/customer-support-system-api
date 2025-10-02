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

// Channel for ticket chat
Broadcast::channel('ticket.{ticketId}', function (User $user, int $ticketId) {
    // Check if user has access to this ticket (owner, assigned agent, or admin)
    $ticket = Ticket::find($ticketId);

    if (!$ticket) {
        return false;
    }

    // User is the ticket owner
    if ($ticket->user_id === $user->id) {
        return true;
    }

    // User is the assigned agent
    if ($ticket->assigned_to === $user->id) {
        return true;
    }

    // User is an admin
    if ($user->role === 'admin') {
        return true;
    }

    return false;
});
