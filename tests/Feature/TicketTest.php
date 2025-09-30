<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use Laravel\Sanctum\Sanctum;

class TicketTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function test_admin_can_get_all_tickets()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        Ticket::factory()->count(5)->for($admin)->create();
        Ticket::factory()->count(3)->for($customer)->create();

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
            ->assertJsonCount(8);
    }

    public function test_customer_can_get_only_their_own_tickets()
    {
        $customer1 = User::factory()->create();
        $customer2 = User::factory()->create();
        Ticket::factory()->count(3)->for($customer1)->create();
        Ticket::factory()->count(2)->for($customer2)->create();

        Sanctum::actingAs($customer1);

        $response = $this->getJson('/api/tickets');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_user_can_create_a_ticket()
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $ticketData = [
            'subject' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
        ];

        $response = $this->postJson('/api/tickets', $ticketData);

        $response->assertStatus(201)
            ->assertJsonFragment($ticketData);

        $this->assertDatabaseHas('tickets', $ticketData);
    }

    public function test_admin_can_update_any_ticket()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $ticket = Ticket::factory()->for($customer)->create();

        Sanctum::actingAs($admin);

        $updateData = ['subject' => 'Updated Subject'];

        $response = $this->putJson("/api/tickets/{$ticket->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('tickets', $updateData);
    }

    public function test_customer_can_update_their_own_ticket()
    {
        $customer = User::factory()->create();
        $ticket = Ticket::factory()->for($customer)->create();

        Sanctum::actingAs($customer);

        $updateData = ['subject' => 'Updated Subject'];

        $response = $this->putJson("/api/tickets/{$ticket->id}", $updateData);

        $response->assertStatus(200)
            ->assertJsonFragment($updateData);

        $this->assertDatabaseHas('tickets', $updateData);
    }

    public function test_customer_cannot_update_another_users_ticket()
    {
        $customer1 = User::factory()->create();
        $customer2 = User::factory()->create();
        $ticket = Ticket::factory()->for($customer2)->create();

        Sanctum::actingAs($customer1);

        $updateData = ['subject' => 'Updated Subject'];

        $response = $this->putJson("/api/tickets/{$ticket->id}", $updateData);

        $response->assertStatus(403);
    }

    public function test_admin_can_delete_any_ticket()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $customer = User::factory()->create();
        $ticket = Ticket::factory()->for($customer)->create();

        Sanctum::actingAs($admin);

        $response = $this->deleteJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('tickets', ['id' => $ticket->id]);
    }

    public function test_customer_cannot_delete_a_ticket()
    {
        $customer = User::factory()->create();
        $ticket = Ticket::factory()->for($customer)->create();

        Sanctum::actingAs($customer);

        $response = $this->deleteJson("/api/tickets/{$ticket->id}");

        $response->assertStatus(403);
    }
}