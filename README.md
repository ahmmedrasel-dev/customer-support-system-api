# Customer Support System — API

This repository contains the backend API for the Customer Support System, built with Laravel. The API powers ticket creation, assignment, comments, attachments, and real-time notifications (Pusher + Laravel Broadcasting).

This README documents implemented features, setup instructions, important environment variables, and how to test the real-time notification flow.

## Implemented Features

-   Authentication with Laravel Sanctum (register/login/logout).
-   Ticket lifecycle: create, view, update, delete.
-   Ticket assignment to admin users with database notifications and broadcast events.
-   Comments and attachments on tickets.
-   Persistent notifications stored in the `notifications` table and real-time delivery via Pusher.
-   Broadcasting channels:
    -   `admin-notifications` (admins)
    -   `user.{id}` (private per-user channel)
-   Events implemented: `TicketCreated`, `TicketAssigned`, `TicketUpdated`, `TicketDeleted`.
-   Admin-only endpoints: customers, tickets, recent tickets (latest 10), admins list for assignment.

## Quick Setup (Development)

Prerequisites:

-   PHP 8.2+
-   Composer
-   MySQL
-   Node.js & npm (front-end client)

1. Install dependencies

```bash
composer install
```

2. Environment

Copy `.env.example` to `.env` and configure values. Important environment variables:

-   APP_URL (e.g. `http://127.0.0.1:8000`)
-   DB_CONNECTION (sqlite/mysql)
-   PUSHER_APP_ID
-   PUSHER_APP_KEY
-   PUSHER_APP_SECRET
-   PUSHER_APP_CLUSTER
-   BROADCAST_CONNECTION=pusher

3. Database

If you use SQLite, point `DB_CONNECTION=sqlite` and create `database/database.sqlite`.

Run migrations and seeders:

```bash
php artisan migrate --seed
```

4. Run the application

```bash
php artisan serve
```

5. Start a queue worker (recommended for queued broadcasts/events)

```bash
php artisan queue:work
```

## Environment (Broadcasting)

This project uses Laravel broadcasting with the Pusher driver. Make sure your `.env` contains the correct Pusher credentials and `BROADCAST_CONNECTION=pusher`.

Frontend clients must authenticate private channels by sending the Bearer token to the Laravel broadcasting auth endpoint (the client is already configured to use `api/broadcasting/auth`).

## API Endpoints (Overview)

Authentication

-   `POST /api/register` — register a new user (returns token)
-   `POST /api/login` — login (returns token)
-   `POST /api/logout` — logout (requires auth)

Tickets

-   `GET /api/tickets` — list tickets (admins see all, customers see own)
-   `POST /api/tickets` — create ticket (auth)
-   `GET /api/tickets/{ticket}` — show ticket
-   `PUT/PATCH /api/tickets/{ticket}` — update ticket (admin)
-   `DELETE /api/tickets/{ticket}` — delete ticket (admin)
-   `POST /api/tickets/{ticket}/assign` — assign ticket to admin (admin only)

Admin routes (prefix `/api/admin`)

-   `GET /api/admin/customers` — get customers list
-   `GET /api/admin/tickets` — list all tickets
-   `GET /api/admin/recent-tickets` — get latest 10 tickets
-   `GET /api/admin/admins` — list admin users (for assignment dropdown)

Notifications

-   `GET /api/notifications` — user notifications
-   `PATCH /api/notifications/{id}/read` — mark a notification read
-   `PATCH /api/notifications/mark-all-read` — mark all notifications read
-   `GET /api/notifications/unread-count` — unread count

## Real-time Notifications (How it works)

1. Events (TicketCreated / TicketAssigned / TicketUpdated / TicketDeleted) create database notifications for relevant users.
2. Events also implement `ShouldBroadcast` and broadcast payloads on:
    - `admin-notifications` channel for admins
    - `user.{id}` private channels for specific users
3. Frontend connects to Pusher and authenticates through `/api/broadcasting/auth` using the Bearer token.
4. When an event is broadcast, the front-end receives the payload and displays an in-app toast and the notification is persisted in the DB.

Important: Ensure `php artisan queue:work` is running if your events are queued.

## Example: Assigning a Ticket

Endpoint: `POST /api/tickets/{ticket}/assign`

Request body:

```json
{ "assigned_to": 3 }
```

Notes:

-   Only admin users may call this endpoint.
-   The assigned user must have `role = 'admin'`.
-   The action creates notifications for the assigned admin and other admins, and broadcasts a `notification.ticket.assigned` event.

## Troubleshooting

-   404 on new routes: clear and re-cache routes after changes: `php artisan route:clear && php artisan route:cache`.
-   403 broadcasting auth: ensure the frontend sends the Bearer token to `/api/broadcasting/auth` and `BROADCAST_CONNECTION` is `pusher`.
-   No real-time events: make sure the queue worker is running.

## Next Improvements (optional)

-   Add integration tests for events and notification flow.
-   Add Postman collection and API documentation.
-   Add pagination and filtering to admin ticket endpoints.

---

If you want, I'll add a matching README for the frontend client that explains how to configure Pusher and the NotificationProvider usage.
