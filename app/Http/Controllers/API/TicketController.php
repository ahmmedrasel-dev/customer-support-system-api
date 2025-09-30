<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use App\Models\Ticket;

use Illuminate\Support\Facades\Gate;

class TicketController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (Gate::allows('is-admin')) {
            $tickets = Ticket::all();
        } else {
            $tickets = auth()->user()->tickets;
        }

        return response()->json($tickets);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'category' => 'nullable|string|max:255',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        $ticket = auth()->user()->tickets()->create($validatedData);

        return response()->json($ticket, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        if (Gate::denies('view', $ticket)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($ticket);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Ticket $ticket)
    {
        if (Gate::denies('update', $ticket)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validatedData = $request->validate([
            'subject' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'category' => 'nullable|string|max:255',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'status' => 'sometimes|in:open,in_progress,resolved,closed',
            'assigned_to' => 'sometimes|exists:users,id',
        ]);

        $ticket->update($validatedData);

        return response()->json($ticket);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        if (Gate::denies('delete', $ticket)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $ticket->delete();

        return response()->json(null, 204);
    }
}
