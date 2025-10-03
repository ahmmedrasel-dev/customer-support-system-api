<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketUpdated
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  /**
   * Create a new event instance.
   */
  public Ticket $ticket;
  public User $updatedBy;
  public array $changes;

  public function __construct(Ticket $ticket, User $updatedBy, array $changes = [])
  {
    $this->ticket = $ticket;
    $this->updatedBy = $updatedBy;
    $this->changes = $changes;

    // Create notifications
    $this->createNotifications();
  }

  private function createNotifications(): void
  {
    $usersToNotify = collect();

    // Always notify the ticket creator
    $usersToNotify->push($this->ticket->user);

    // Notify assigned admin if exists
    if ($this->ticket->assigned_to) {
      $assignedAdmin = User::find($this->ticket->assigned_to);
      if ($assignedAdmin) {
        $usersToNotify->push($assignedAdmin);
      }
    }

    // Notify all admins for status changes
    if (isset($this->changes['status'])) {
      $admins = User::where('role', 'admin')->get();
      $usersToNotify = $usersToNotify->merge($admins);
    }

    // Remove duplicates
    $usersToNotify = $usersToNotify->unique('id');

    foreach ($usersToNotify as $user) {
      // Skip the user who made the update
      if ($user->id === $this->updatedBy->id) continue;

      $message = $this->generateMessage($user);

      Notification::create([
        'type' => 'ticket_updated',
        'title' => 'Ticket Updated',
        'message' => $message,
        'data' => [
          'ticket_id' => $this->ticket->id,
          'changes' => $this->changes,
          'updated_by' => $this->updatedBy->name,
        ],
        'action_url' => $user->role === 'admin' ? "/admin/tickets/{$this->ticket->id}" : "/tickets/{$this->ticket->id}",
        'user_id' => $user->id,
        'sender_id' => $this->updatedBy->id,
      ]);
    }
  }

  private function generateMessage(User $user): string
  {
    $changes = [];

    if (isset($this->changes['status'])) {
      $changes[] = "status changed to '{$this->changes['status']}'";
    }
    if (isset($this->changes['priority'])) {
      $changes[] = "priority changed to '{$this->changes['priority']}'";
    }
    if (isset($this->changes['title'])) {
      $changes[] = "title updated";
    }
    if (isset($this->changes['description'])) {
      $changes[] = "description updated";
    }

    $changeText = implode(', ', $changes);

    if ($user->role === 'admin') {
      return "Ticket '{$this->ticket->title}' {$changeText} by {$this->updatedBy->name}";
    } else {
      return "Your ticket '{$this->ticket->title}' has been {$changeText}";
    }
  }

  public function broadcastOn(): array
  {
    $channels = [new Channel('admin-notifications')];

    // Also broadcast to customer
    $channels[] = new Channel("user.{$this->ticket->user_id}");

    // And to assigned admin if exists
    if ($this->ticket->assigned_to) {
      $channels[] = new Channel("user.{$this->ticket->assigned_to}");
    }

    return $channels;
  }

  public function broadcastAs(): string
  {
    return 'notification.ticket.updated';
  }

  public function broadcastWith(): array
  {
    return [
      'type' => 'ticket_updated',
      'title' => 'Ticket Updated',
      'message' => $this->generateMessage(auth()->user() ?? new User(['role' => 'guest'])),
      'action_url' => auth()->user()?->role === 'admin' ? "/admin/tickets/{$this->ticket->id}" : "/tickets/{$this->ticket->id}",
      'data' => [
        'ticket_id' => $this->ticket->id,
        'changes' => $this->changes,
        'updated_by' => $this->updatedBy->name,
        'updated_at' => $this->ticket->updated_at,
      ],
    ];
  }
}
