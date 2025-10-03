<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'subject',
    'description',
    'category',
    'priority',
    'status',
    'assigned_to',
  ];

  protected $casts = [
    'assigned_at' => 'datetime',
  ];

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function comments()
  {
    return $this->hasMany(Comment::class);
  }

  public function attachments()
  {
    return $this->hasMany(Attachment::class);
  }

  public function chatMessages(): HasMany
  {
    return $this->hasMany(ChatMessage::class);
  }

  public function assignedUser(): BelongsTo
  {
    return $this->belongsTo(User::class, 'assigned_to');
  }
}
