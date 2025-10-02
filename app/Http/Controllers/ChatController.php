<?php

namespace App\Http\Controllers;

use App\Models\ChatMessage;
use App\Models\Ticket;
use App\Events\NewChatMessage;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
  /**
   * Get all messages for a ticket
   */
  public function getMessages(Ticket $ticket): JsonResponse
  {
    // Check if the user has access to this ticket
    if (Gate::denies('view', $ticket)) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $messages = $ticket->chatMessages()
      ->with('user:id,name,role')
      ->orderBy('created_at')
      ->get();

    return response()->json($messages);
  }

  /**
   * Store a new chat message
   */
  public function sendMessage(Request $request, Ticket $ticket): JsonResponse
  {
    // Check if the user has access to this ticket
    if (Gate::denies('view', $ticket)) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $validated = $request->validate([
      'message' => 'required|string',
      'type' => 'required|in:text,file,system',
    ]);

    $message = new ChatMessage([
      'ticket_id' => $ticket->id,
      'user_id' => Auth::id(),
      'message' => $validated['message'],
      'type' => $validated['type'],
      'is_read' => false,
    ]);

    $message->save();

    // Load the user relationship
    $message->load('user:id,name,role');

    // Broadcast the new message (with error handling)
    try {
      Log::info('Broadcasting new chat message', [
        'message_id' => $message->id,
        'ticket_id' => $message->ticket_id,
        'user_id' => $message->user_id,
        'message_content' => $message->message
      ]);

      // Dispatch the event directly
      broadcast(new NewChatMessage($message))->toOthers();


      Log::info('Chat message event dispatched successfully');
    } catch (\Exception $e) {
      // Log the error but don't fail the request
      Log::warning('Failed to broadcast message: ' . $e->getMessage(), [
        'message_id' => $message->id,
        'exception' => $e->getTraceAsString()
      ]);
    }

    return response()->json($message, 201);
  }

  /**
   * Upload a file attachment
   */
  public function uploadFile(Request $request, Ticket $ticket): JsonResponse
  {
    // Check if the user has access to this ticket
    if (Gate::denies('view', $ticket)) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    $request->validate([
      'file' => 'required|file|max:10240', // Max 10MB
    ]);

    $file = $request->file('file');
    $path = $file->store('chat-attachments', 'public');
    $url = Storage::url($path);

    return response()->json([
      'url' => $url,
      'filename' => $file->getClientOriginalName(),
    ]);
  }

  /**
   * Mark all messages in a ticket as read for the current user
   */
  public function markAsRead(Ticket $ticket): JsonResponse
  {
    // Check if the user has access to this ticket
    if (Gate::denies('view', $ticket)) {
      return response()->json(['message' => 'Unauthorized'], 403);
    }

    // Mark messages from other users as read
    ChatMessage::where('ticket_id', $ticket->id)
      ->where('user_id', '!=', Auth::id())
      ->where('is_read', false)
      ->update(['is_read' => true]);

    return response()->json(['message' => 'Messages marked as read']);
  }
}
