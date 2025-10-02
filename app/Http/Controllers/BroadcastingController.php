<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

class BroadcastingController extends Controller
{
  /**
   * Authenticate the request for channel access.
   */
  public function authenticate(Request $request): JsonResponse
  {


    try {
      // Get the authenticated user
      $user = $request->user();
      if (!$user) {
        return response()->json(['error' => 'Unauthenticated'], 401);
      }

      // Pusher sends data as form-encoded, so we need to get it from the request properly
      $channelName = $request->input('channel_name') ?: $request->get('channel_name');
      $socketId = $request->input('socket_id') ?: $request->get('socket_id');

      // If still null, try to parse raw input
      if (!$channelName || !$socketId) {
        parse_str($request->getContent(), $data);
        $channelName = $data['channel_name'] ?? null;
        $socketId = $data['socket_id'] ?? null;
      }


      // Use Laravel's broadcasting authentication
      $auth = Broadcast::auth($request);

      return response()->json($auth);
    } catch (\Exception $e) {

      return response()->json(['error' => 'Authentication failed: ' . $e->getMessage()], 403);
    }
  }
}
