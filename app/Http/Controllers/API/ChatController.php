<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Ticket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Gate;

class ChatController extends Controller
{
    public function getMessages(Request $request, Ticket $ticket): JsonResponse
    {
        // Check if user can access this ticket
        if (!Gate::allows('view-ticket', [$ticket, $request->user()])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $messages = $ticket->chatMessages()
            ->with('user:id,name,email,role')
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    public function sendMessage(Request $request, Ticket $ticket): JsonResponse
    {
        // Check if user can access this ticket
        if (!Gate::allows('view-ticket', [$ticket, $request->user()])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'message' => 'required|string|max:1000',
            'type' => 'in:text,file,system',
        ]);

        $message = ChatMessage::create([
            'ticket_id' => $ticket->id,
            'user_id' => $request->user()->id,
            'message' => $request->message,
            'type' => $request->type ?? 'text',
        ]);

        // Broadcast the message
        broadcast(new MessageSent($message))->toOthers();

        return response()->json([
            'message' => 'Message sent successfully',
            'data' => $message->load('user:id,name,email,role'),
        ]);
    }

    public function markAsRead(Request $request, Ticket $ticket): JsonResponse
    {
        // Check if user can access this ticket
        if (!Gate::allows('view-ticket', [$ticket, $request->user()])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $ticket->chatMessages()
            ->where('user_id', '!=', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['message' => 'Messages marked as read']);
    }
}
