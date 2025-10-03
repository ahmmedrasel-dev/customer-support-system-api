<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
  use HasFactory;

  protected $fillable = [
    'type',
    'title',
    'message',
    'data',
    'action_url',
    'user_id',
    'sender_id',
    'read',
    'read_at',
  ];

  protected $casts = [
    'data' => 'array',
    'read' => 'boolean',
    'read_at' => 'datetime',
  ];

  public function user(): BelongsTo
  {
    return $this->belongsTo(User::class);
  }

  public function sender(): BelongsTo
  {
    return $this->belongsTo(User::class, 'sender_id');
  }

  public function markAsRead(): void
  {
    $this->update([
      'read' => true,
      'read_at' => now(),
    ]);
  }
}
