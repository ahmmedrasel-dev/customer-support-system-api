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
    // Log that the endpoint was called
    Log::info('🎯 Broadcasting auth endpoint called', [
      'method' => $request->method(),
      'url' => $request->fullUrl(),
      'headers' => $request->headers->all(),
      'content_type' => $request->header('Content-Type'),
      'raw_content' => $request->getContent(),
      'all_data' => $request->all()
    ]);

    try {
      // Get the authenticated user
      $user = $request->user();
      if (!$user) {
        Log::error('Broadcasting auth: No authenticated user');
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

      // Log the authentication attempt with request details
      Log::info('🔐 Broadcasting auth attempt', [
        'user_id' => $user->id,
        'user_role' => $user->role,
        'channel_name' => $channelName,
        'socket_id' => $socketId,
        'timestamp' => now()->toISOString()
      ]);

      // Use Laravel's broadcasting authentication
      $auth = Broadcast::auth($request);

      Log::info('Broadcasting auth successful', [
        'user_id' => $user->id,
        'auth_result' => $auth
      ]);

      return response()->json($auth);
    } catch (\Exception $e) {
      Log::error('Broadcasting auth error: ' . $e->getMessage(), [
        'user_id' => $request->user()?->id,
        'channel_name' => $request->input('channel_name'),
        'socket_id' => $request->input('socket_id'),
        'trace' => $e->getTraceAsString(),
        'all_inputs' => $request->all(),
        'raw_content' => $request->getContent()
      ]);
      return response()->json(['error' => 'Authentication failed: ' . $e->getMessage()], 403);
    }
  }
}
