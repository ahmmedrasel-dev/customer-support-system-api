<?php

namespace App\Events;

use App\Models\Notification;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketAssigned implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public Ticket $ticket;
  public User $assignedBy;
  public User $assignedTo;
  public User $assignedBy;
  public User $assignedTo;

  public function __construct(Ticket $ticket, User $assignedBy, User $assignedTo)
  {
    $this->ticket = $ticket;
    $this->assignedBy = $assignedBy;
    $this->assignedTo = $assignedTo;

    // Notify the assigned admin
    Notification::create([
      'type' => 'ticket_assigned',
      'title' => 'Ticket Assigned to You',
      'message' => "Admin {$assignedBy->name} assigned ticket '{$ticket->title}' to you",
      'data' => [
        'ticket_id' => $ticket->id,
        'assigned_by' => $assignedBy->name,
        'ticket_title' => $ticket->title,
      ],
      'action_url' => "/admin/tickets/{$ticket->id}",
      'user_id' => $assignedTo->id,
      'sender_id' => $assignedBy->id,
    ]);

    // Notify other admins about the assignment
    $otherAdmins = User::where('role', 'admin')
      ->where('id', '!=', $assignedTo->id)
      ->get();

    foreach ($otherAdmins as $admin) {
      Notification::create([
        'type' => 'ticket_assigned_other',
        'title' => 'Ticket Assignment Update',
        'message' => "{$assignedBy->name} assigned ticket '{$ticket->title}' to {$assignedTo->name}",
        'data' => [
          'ticket_id' => $ticket->id,
          'assigned_by' => $assignedBy->name,
          'assigned_to' => $assignedTo->name,
        ],
        'action_url' => "/admin/tickets/{$ticket->id}",
        'user_id' => $admin->id,
        'sender_id' => $assignedBy->id,
      ]);
    }
  }

  public function broadcastOn(): array
  {
    return [
      new Channel('admin-notifications'),
      new Channel("user.{$this->assignedTo->id}"),
    ];
  }

  public function broadcastAs(): string
  {
    return 'notification.ticket.assigned';
  }

  public function broadcastWith(): array
  {
    return [
      'type' => 'ticket_assigned',
      'title' => $this->assignedTo->id === auth()->id() ? 'Ticket Assigned to You' : 'Ticket Assignment Update',
      'message' => "{$this->assignedBy->name} assigned ticket '{$this->ticket->title}' to {$this->assignedTo->name}",
      'action_url' => "/admin/tickets/{$this->ticket->id}",
      'data' => [
        'ticket_id' => $this->ticket->id,
        'assigned_by' => $this->assignedBy->name,
        'assigned_to' => $this->assignedTo->name,
        'assigned_at' => $this->ticket->assigned_at,
      ],
    ];
  }
}
