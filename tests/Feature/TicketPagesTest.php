<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_ticket_counts_and_a_link_to_tickets(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $category = Category::factory()->create();

        Ticket::factory()->create([
            'created_by' => $agent->id,
            'assigned_to' => $agent->id,
            'category_id' => $category->id,
            'status' => 'open',
        ]);

        $response = $this->actingAs($agent)->get(route('dashboard'));

        $response->assertOk();
        $response->assertSee('Tickets');
        $response->assertSee('Open');
        $response->assertSee(route('tickets.index'), false);
    }

    public function test_employees_only_see_their_own_tickets(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $other = User::factory()->create(['role' => 'employee']);
        $category = Category::factory()->create();

        $own = Ticket::factory()->create([
            'created_by' => $employee->id,
            'category_id' => $category->id,
            'title' => 'Mijn printer',
        ]);

        $foreign = Ticket::factory()->create([
            'created_by' => $other->id,
            'category_id' => $category->id,
            'title' => 'Geheim ticket',
        ]);

        $this->actingAs($employee)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('Mijn printer')
            ->assertDontSee('Geheim ticket');

        $this->actingAs($employee)
            ->get(route('tickets.show', $foreign))
            ->assertForbidden();

        $this->actingAs($employee)
            ->get(route('tickets.show', $own))
            ->assertOk();
    }

    public function test_agent_can_create_a_ticket(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $category = Category::factory()->create();

        $response = $this->actingAs($agent)->post(route('tickets.store'), [
            'title' => 'Wifi ligt eruit',
            'description' => 'Op de eerste verdieping is geen verbinding.',
            'category_id' => $category->id,
            'priority' => 'high',
            'status' => 'open',
            'assigned_to' => $agent->id,
        ]);

        $ticket = Ticket::first();

        $response->assertRedirect(route('tickets.show', $ticket));
        $this->assertSame('Wifi ligt eruit', $ticket->title);
        $this->assertSame($agent->id, $ticket->created_by);
    }
}
