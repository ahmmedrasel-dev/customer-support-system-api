<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Ticket;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
          'name' => 'required|string|max:255',
          'email' => 'required|string|email|max:255|unique:users',
          'password' => 'required|string|min:8',
          'role' => 'sometimes|in:admin,customer'
        ]);

        $user = User::create([
          'name' => $validatedData['name'],
          'email' => $validatedData['email'],
          'password' => Hash::make($validatedData['password']),
          'role' => $validatedData['role'] ?? 'customer',
        ]);
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
          'access_token' => $token,
          'token_type' => 'Bearer',
        ]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
              'message' => 'Invalid login details'
            ], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
          'access_token' => $token,
          'message' => 'Login successful',
          'token_type' => 'Bearer',
          'user' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
          ],
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out']);
    }

    public function getCustomers(Request $request)
    {
        // Check if user is admin using Gate
        if (!Gate::allows('is-admin', $request->user())) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        // Get all customers with ticket counts
        $customers = User::where('role', 'customer')
            ->withCount(['tickets', 'tickets as active_tickets_count' => function ($query) {
                $query->whereIn('status', ['open', 'in_progress']);
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($customers);
    }

    public function getTickets(Request $request)
    {
        // Check if user is admin using Gate
        if (!Gate::allows('is-admin', $request->user())) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        // Get all tickets with user and activity counts
        $tickets = Ticket::with(['user:id,name,email'])
            ->withCount(['comments', 'attachments'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($tickets);
    }

    public function getTicketDetail(Request $request, Ticket $ticket)
    {
        // Check if user is admin or ticket owner
        if (!Gate::allows('view', [$ticket, $request->user()])) {
            return response()->json([
                'message' => 'Unauthorized. You can only view your own tickets.'
            ], 403);
        }

        // Load ticket with relationships
        $ticket->load(['user:id,name,email', 'comments.user:id,name,email,role', 'attachments']);

        return response()->json($ticket);
    }

    public function updateTicket(Request $request, Ticket $ticket)
    {
        // Check if user is admin
        if (!Gate::allows('is-admin', $request->user())) {
            return response()->json([
                'message' => 'Unauthorized. Admin access required.'
            ], 403);
        }

        $validatedData = $request->validate([
            'status' => 'sometimes|in:open,in_progress,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
            'assigned_to' => 'sometimes|nullable|exists:users,id',
        ]);

        $ticket->update($validatedData);

        return response()->json($ticket);
    }
}
