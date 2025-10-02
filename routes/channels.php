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
  \Log::info('Channel authorization attempt', [
    'user_id' => $user->id,
    'user_role' => $user->role,
    'ticket_id' => $ticketId
  ]);

  // Check if user has access to this ticket (owner, assigned agent, or admin)
  $ticket = Ticket::find($ticketId);

  if (!$ticket) {
    \Log::warning('Ticket not found for channel authorization', ['ticket_id' => $ticketId]);
    return false;
  }

  \Log::info('Ticket found', [
    'ticket_id' => $ticket->id,
    'ticket_user_id' => $ticket->user_id,
    'ticket_assigned_to' => $ticket->assigned_to
  ]);

  // User is the ticket owner
  if ($ticket->user_id === $user->id) {
    \Log::info('User authorized as ticket owner');
    return ['id' => $user->id, 'name' => $user->name, 'role' => $user->role];
  }

  // User is the assigned agent
  if ($ticket->assigned_to === $user->id) {
    \Log::info('User authorized as assigned agent');
    return ['id' => $user->id, 'name' => $user->name, 'role' => $user->role];
  }

  // User is an admin
  if ($user->role === 'admin') {
    \Log::info('User authorized as admin');
    return ['id' => $user->id, 'name' => $user->name, 'role' => $user->role];
  }

  \Log::warning('User not authorized for ticket channel', [
    'user_id' => $user->id,
    'ticket_user_id' => $ticket->user_id,
    'ticket_assigned_to' => $ticket->assigned_to,
    'user_role' => $user->role
  ]);

  return false;
});
