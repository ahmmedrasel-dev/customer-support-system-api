<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Ticket;
use Laravel\Sanctum\Sanctum;

class CommentTest extends TestCase
{
  use RefreshDatabase, WithFaker;

  public function test_user_can_add_a_comment_to_their_own_ticket()
  {
    $user = User::factory()->create();
    $ticket = Ticket::factory()->for($user)->create();

    Sanctum::actingAs($user);

    $commentData = [
      'ticket_id' => $ticket->id,
      'body' => $this->faker->paragraph,
    ];

    $response = $this->postJson('/api/comments', $commentData);

    $response->assertStatus(201)
      ->assertJsonFragment($commentData);

    $this->assertDatabaseHas('comments', $commentData);
  }

  public function test_admin_can_add_a_comment_to_any_ticket()
  {
    $admin = User::factory()->create(['role' => 'admin']);
    $customer = User::factory()->create();
    $ticket = Ticket::factory()->for($customer)->create();

    Sanctum::actingAs($admin);

    $commentData = [
      'ticket_id' => $ticket->id,
      'body' => $this->faker->paragraph,
    ];

    $response = $this->postJson('/api/comments', $commentData);

    $response->assertStatus(201)
      ->assertJsonFragment($commentData);

    $this->assertDatabaseHas('comments', $commentData);
  }

  public function test_user_cannot_add_a_comment_to_another_users_ticket()
  {
    $customer1 = User::factory()->create();
    $customer2 = User::factory()->create();
    $ticket = Ticket::factory()->for($customer2)->create();

    Sanctum::actingAs($customer1);

    $commentData = [
      'ticket_id' => $ticket->id,
      'body' => $this->faker->paragraph,
    ];

    $response = $this->postJson('/api/comments', $commentData);

    $response->assertStatus(403);
  }
}
