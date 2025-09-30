<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\Comment;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller
{
  /**
   * Store a newly created resource in storage.
   */
  public function store(Request $request)
  {
    $validatedData = $request->validate([
      'file' => 'required|file',
      'ticket_id' => 'nullable|exists:tickets,id',
      'comment_id' => 'nullable|exists:comments,id',
    ]);

    if (!$request->has('ticket_id') && !$request->has('comment_id')) {
      return response()->json(['message' => 'Ticket ID or Comment ID is required.'], 422);
    }

    if ($request->has('ticket_id')) {
      $ticket = Ticket::findOrFail($validatedData['ticket_id']);
      if (Gate::denies('add-attachment', $ticket)) {
        return response()->json(['message' => 'Forbidden'], 403);
      }
    }

    if ($request->has('comment_id')) {
      $comment = Comment::findOrFail($validatedData['comment_id']);
      if (Gate::denies('add-attachment', $comment->ticket)) {
        return response()->json(['message' => 'Forbidden'], 403);
      }
    }

    $file = $request->file('file');
    $path = $file->store('attachments');

    $attachment = Attachment::create([
      'ticket_id' => $validatedData['ticket_id'] ?? null,
      'comment_id' => $validatedData['comment_id'] ?? null,
      'file_path' => $path,
      'file_name' => $file->getClientOriginalName(),
      'mime_type' => $file->getMimeType(),
      'size' => $file->getSize(),
    ]);

    return response()->json($attachment, 201);
  }
}
