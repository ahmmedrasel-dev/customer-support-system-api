<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketDeleted
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public Ticket $ticket;
  public User $deletedBy;

  public function __construct(Ticket $ticket, User $deletedBy)
  {
    $this->ticket = $ticket;
    $this->deletedBy = $deletedBy;

    // Notify all admins about deletion
    $admins = User::where('role', 'admin')->get();
    foreach ($admins as $admin) {
      Notification::create([
        'type' => 'ticket_deleted',
        'title' => 'Ticket Deleted',
        'message' => "Admin {$deletedBy->name} deleted ticket '{$ticket->title}' (ID: {$ticket->id})",
        'data' => [
          'ticket_id' => $ticket->id,
          'ticket_title' => $ticket->title,
          'deleted_by' => $deletedBy->name,
        ],
        'action_url' => "/admin/tickets", // Go to tickets list since ticket is deleted
        'user_id' => $admin->id,
        'sender_id' => $deletedBy->id,
      ]);
    }

    // Notify the customer
    Notification::create([
      'type' => 'ticket_deleted',
      'title' => 'Ticket Closed',
      'message' => "Your ticket '{$ticket->title}' has been closed by support team",
      'data' => [
        'ticket_id' => $ticket->id,
        'ticket_title' => $ticket->title,
      ],
      'action_url' => "/tickets", // Go to tickets list
      'user_id' => $ticket->user_id,
      'sender_id' => $deletedBy->id,
    ]);
  }

  public function broadcastOn(): array
  {
    return [
      new Channel('admin-notifications'),
      new Channel("user.{$this->ticket->user_id}"),
    ];
  }

  public function broadcastAs(): string
  {
    return 'notification.ticket.deleted';
  }

  public function broadcastWith(): array
  {
    return [
      'type' => 'ticket_deleted',
      'title' => auth()->user()?->role === 'admin' ? 'Ticket Deleted' : 'Ticket Closed',
      'message' => auth()->user()?->role === 'admin'
        ? "Admin {$this->deletedBy->name} deleted ticket '{$this->ticket->title}'"
        : "Your ticket '{$this->ticket->title}' has been closed",
      'action_url' => auth()->user()?->role === 'admin' ? "/admin/tickets" : "/tickets",
      'data' => [
        'ticket_id' => $this->ticket->id,
        'ticket_title' => $this->ticket->title,
        'deleted_by' => $this->deletedBy->name,
      ],
    ];
  }
}
