<?php

namespace App\Events;

use App\Models\ChatMessage;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewChatMessage implements ShouldBroadcastNow
{
  use Dispatchable;
  use InteractsWithSockets;
  use SerializesModels;

  /**
   * Create a new event instance.
   */
  public function __construct(
    public ChatMessage $message
  ) {}

  /**
   * Get the channels the event should broadcast on.
   *
   * @return array<int, \Illuminate\Broadcasting\Channel>
   */
  public function broadcastOn(): array
  {
    return [
      new Channel('ticket.' . $this->message->ticket_id),
    ];
  }

  /**
   * The event's broadcast name.
   */
  public function broadcastAs(): string
  {
    return 'message.sent';
  }

  /**
   * Get the data to broadcast.
   *
   * @return array<string, mixed>
   */
  public function broadcastWith(): array
  {
    // Ensure the user relationship is loaded
    if (!$this->message->relationLoaded('user')) {
      $this->message->load('user:id,name,role');
    }

    return [
      'id' => $this->message->id,
      'message' => $this->message->message,
      'type' => $this->message->type,
      'is_read' => $this->message->is_read,
      'created_at' => $this->message->created_at->toISOString(),
      'updated_at' => $this->message->updated_at->toISOString(),
      'user' => [
        'id' => $this->message->user->id,
        'name' => $this->message->user->name,
        'role' => $this->message->user->role,
      ],
    ];
  }
}
