<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

use App\Models\Comment;
use App\Models\Ticket;

class CommentController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'ticket_id' => 'required|exists:tickets,id',
            'body' => 'required|string',
        ]);

        $ticket = Ticket::findOrFail($validatedData['ticket_id']);

        if (!auth()->user()->tokenCan('is-admin') && $ticket->user_id !== auth()->id()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $comment = $ticket->comments()->create([
            'user_id' => auth()->id(),
            'body' => $validatedData['body'],
        ]);

        return response()->json($comment, 201);
    }
