<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
  public function index()
  {
    $notifications = Notification::where('user_id', Auth::id())
      ->with('sender')
      ->orderBy('created_at', 'desc')
      ->paginate(20);

    return response()->json($notifications);
  }

  public function markAsRead($id)
  {
    $notification = Notification::where('user_id', Auth::id())
      ->findOrFail($id);

    $notification->markAsRead();

    return response()->json(['message' => 'Notification marked as read']);
  }

  public function markAllAsRead()
  {
    Notification::where('user_id', Auth::id())
      ->where('read', false)
      ->update([
        'read' => true,
        'read_at' => now(),
      ]);

    return response()->json(['message' => 'All notifications marked as read']);
  }

  public function unreadCount()
  {
    $count = Notification::where('user_id', Auth::id())
      ->where('read', false)
      ->count();

    return response()->json(['count' => $count]);
  }

  public function destroy($id)
  {
    $notification = Notification::where('user_id', Auth::id())
      ->findOrFail($id);

    $notification->delete();

    return response()->json(['message' => 'Notification deleted']);
  }
}
