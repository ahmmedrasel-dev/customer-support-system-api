<?php

namespace App\Http\Controllers\API;

use App\Events\TicketCreated;
use App\Events\TicketUpdated;
use App\Events\TicketAssigned;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;

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
      'file' => 'nullable|file|max:10240', // Max 10MB file size
    ]);

    // Create ticket
    $ticket = auth()->user()->tickets()->create([
      'subject' => $validatedData['subject'],
      'description' => $validatedData['description'],
      'category' => $validatedData['category'] ?? null,
      'priority' => $validatedData['priority'] ?? 'low',
    ]);


    // Handle file upload
    if ($request->hasFile('file')) {
      $file = $request->file('file');
      $path = $file->store('attachments', 'public');

      // Create attachment record
      $ticket->attachments()->create([
        'file_path' => $path,
        'file_name' => $file->getClientOriginalName(),
        'mime_type' => $file->getClientMimeType(),
        'size' => $file->getSize(),
      ]);
    }

    // Load the attachment relationship
    $ticket->load('attachments');

    broadcast(new TicketCreated($ticket))->toOthers();

    return response()->json($ticket->load('user'), 201);
  }

  /**
   * Display the specified resource.
   */
  public function show(Ticket $ticket)
  {
    if (Gate::denies('view', $ticket)) {
      return response()->json(['message' => 'Forbidden'], 403);
    }

    // Load relationships for the ticket detail view
    $ticket->load(['user:id,name,email', 'comments.user:id,name,email,role', 'attachments']);

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

    // Store old data before updating
    $oldData = $ticket->toArray();
    $ticket->update($validatedData);

    $changes = [];
    foreach ($validatedData as $key => $value) {
      if ($oldData[$key] !== $value) {
        $changes[$key] = $value;
      }
    }

    if (!empty($changes)) {
      broadcast(new TicketUpdated($ticket, Auth::user(), $changes))->toOthers();
    }

    return response()->json($ticket->load(['user', 'assignedUser']));
  }


  public function assign(Request $request, Ticket $ticket)
  {
    // Only admins may assign tickets
    if (!Gate::allows('is-admin', $request->user())) {
      return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
    }

    $request->validate([
      'assigned_to' => 'required|exists:users,id',
    ]);

    $assignedTo = User::findOrFail($request->assigned_to);

    // Ensure the assigned user is an admin
    if ($assignedTo->role !== 'admin') {
      return response()->json(['message' => 'Assigned user must be an admin.'], 422);
    }

    $oldData = $ticket->toArray();

    // Update assignment and assigned_at. Do not set an invalid status value.
    $ticket->update([
      'assigned_to' => $assignedTo->id,
      'status' => $ticket->status === 'open' ? 'in_progress' : $ticket->status,
    ]);

    // Broadcast assignment event
    broadcast(new TicketAssigned($ticket, Auth::user(), $assignedTo))->toOthers();

    return response()->json($ticket->load(['user', 'assignedUser']));
  }

  /**
   * Remove the specified resource from storage.
   */
  public function destroy(Ticket $ticket)
  {
    if (Gate::denies('delete', $ticket)) {
      return response()->json(['message' => 'Forbidden'], 403);
    }
    broadcast(new TicketDeleted($ticket, Auth::user()))->toOthers();
    $ticket->delete();

    return response()->json(null, 204);
  }
}
