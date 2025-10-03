<?php

namespace App\Events;

use App\Models\Notification;
use App\Models\Ticket as TicketModel;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TicketUpdated implements ShouldBroadcast
{
  use Dispatchable, InteractsWithSockets, SerializesModels;

  public TicketModel $ticket;
  public User $updatedBy;
  public array $changes;

  public function __construct(TicketModel $ticket, User $updatedBy, array $changes = [])
  {
    $this->ticket = $ticket;
    $this->updatedBy = $updatedBy;
    $this->changes = $changes;

    // Create notifications
    $this->createNotifications();
  }

  private function createNotifications(): void
  {
    \Log::info('Creating notifications for ticket update', [
      'ticket_id' => $this->ticket->id,
      'changes' => $this->changes,
      'updated_by' => $this->updatedBy->id
    ]);

    $usersToNotify = collect();

    // Always notify the ticket creator
    $usersToNotify->push($this->ticket->user);
    \Log::info('Added ticket creator to notify list', ['user_id' => $this->ticket->user->id]);

    // Notify assigned admin if exists
    if ($this->ticket->assigned_to) {
      $assignedAdmin = User::find($this->ticket->assigned_to);
      if ($assignedAdmin) {
        $usersToNotify->push($assignedAdmin);
        \Log::info('Added assigned admin to notify list', ['user_id' => $assignedAdmin->id]);
      }
    }

    // Notify all admins for status changes
    if (isset($this->changes['status'])) {
      $admins = User::where('role', 'admin')->get();
      $usersToNotify = $usersToNotify->merge($admins);
      \Log::info('Added admins for status change', ['admin_count' => $admins->count()]);
    }

    // Remove duplicates
    $usersToNotify = $usersToNotify->unique('id');
    \Log::info('Users to notify after deduplication', ['count' => $usersToNotify->count(), 'user_ids' => $usersToNotify->pluck('id')->toArray()]);

    foreach ($usersToNotify as $user) {
      // Skip the user who made the update
      if ($user->id === $this->updatedBy->id) {
        \Log::info('Skipping notification for sender', ['user_id' => $user->id]);
        continue;
      }

      $message = $this->generateMessage($user);

      $notification = Notification::create([
        'type' => 'ticket_updated',
        'title' => 'Ticket Updated',
        'message' => $message,
        'data' => [
          'ticket_id' => $this->ticket->id,
          'changes' => $this->changes,
          'updated_by' => $this->updatedBy->name,
        ],
        'action_url' => $user->role === 'admin' ? "/admin/tickets/{$this->ticket->id}" : "/customer/tickets/{$this->ticket->id}",
        'user_id' => $user->id,
        'sender_id' => $this->updatedBy->id,
      ]);

      \Log::info('Notification created', ['notification_id' => $notification->id, 'for_user' => $user->id]);
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
    if (isset($this->changes['subject'])) {
      $changes[] = "subject updated";
    }
    if (isset($this->changes['description'])) {
      $changes[] = "description updated";
    }

    $changeText = implode(', ', $changes);

    if ($user->role === 'admin') {
      return "Ticket '{$this->ticket->subject}' {$changeText} by {$this->updatedBy->name}";
    } else {
      return "Your ticket '{$this->ticket->subject}' has been {$changeText}";
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
      'action_url' => auth()->user()?->role === 'admin' ? "/admin/tickets/{$this->ticket->id}" : "/customer/tickets/{$this->ticket->id}",
      'data' => [
        'ticket_id' => $this->ticket->id,
        'changes' => $this->changes,
        'updated_by' => $this->updatedBy->name,
        'updated_at' => $this->ticket->updated_at,
      ],
    ];
  }
}
