<?php

namespace App\Events;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketCreated
{
  use Dispatchable, InteractsWithSockets, SerializesModels;
  public Ticket $ticket;
  /**
   * Create a new event instance.
   */
  public function __construct(Ticket $ticket)
  {
    $this->ticket = $ticket;

    // Create notification fot all admins
    $admins = User::where('role', 'admin')->get();
    foreach ($admins as $admin) {
      Notification::create([
        'type' => 'ticket_created',
        'title' => 'New Ticket Created',
        'message' => "Customer {$ticket->user->name} created a new ticket: {$ticket->title}",
        'data' => ['ticket_id' => $ticket->id, 'customer_name' => $ticket->user->name, 'priority' => $ticket->priority],
        'action_url' => '/admin/tickets/' . $ticket->id,
        'user_id' => $admin->id,
        'sender_id' => $ticket->user_id,
      ]);
    }
  }

  /**
   * Get the channels the event should broadcast on.
   *
   * @return array<int, \Illuminate\Broadcasting\Channel>
   */
  public function broadcastOn(): array
  {
    return [
      new Channel('admin-notifications'),
    ];
  }


  public function broadcastAs(): string
  {
    return 'notification.ticket.created';
  }


  public function broadcastWith(): array
  {
    return [
      'type' => 'ticket_created',
      'title' => 'New Support Ticket',
      'message' => "Customer {$this->ticket->user->name} created: {$this->ticket->title}",
      'action_url' => "/admin/tickets/{$this->ticket->id}",
      'data' => [
        'ticket_id' => $this->ticket->id,
        'customer_name' => $this->ticket->user->name,
        'priority' => $this->ticket->priority,
        'created_at' => $this->ticket->created_at,
      ],
    ];
  }
}
