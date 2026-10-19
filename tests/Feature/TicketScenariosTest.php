<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TicketScenariosTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $ticket = Ticket::factory()->create();

        $this->get(route('tickets.index'))->assertRedirect(route('login'));
        $this->get(route('tickets.create'))->assertRedirect(route('login'));
        $this->get(route('tickets.show', $ticket))->assertRedirect(route('login'));
        $this->post(route('tickets.store'), [])->assertRedirect(route('login'));
    }

    public function test_unverified_users_cannot_open_tickets(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)
            ->get(route('tickets.index'))
            ->assertRedirect(route('verification.notice'));
    }

    public function test_employee_creates_a_ticket_that_stays_open_and_unassigned(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $agent = User::factory()->create(['role' => 'agent']);
        $category = Category::factory()->create();

        $this->actingAs($employee)
            ->post(route('tickets.store'), [
                'title' => 'Muis kapot',
                'description' => 'De muis klikt niet meer.',
                'category_id' => $category->id,
                'priority' => 'low',
                'status' => 'closed',
                'assigned_to' => $agent->id,
            ])
            ->assertRedirect();

        $ticket = Ticket::first();

        $this->assertSame($employee->id, $ticket->created_by);
        $this->assertSame('open', $ticket->status);
        $this->assertNull($ticket->assigned_to);
    }

    public function test_employee_can_update_and_delete_only_their_own_ticket(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $agent = User::factory()->create(['role' => 'agent']);
        $category = Category::factory()->create();

        $own = Ticket::factory()->create([
            'created_by' => $employee->id,
            'category_id' => $category->id,
            'status' => 'open',
            'assigned_to' => null,
            'title' => 'Mijn ticket',
        ]);

        $foreign = Ticket::factory()->create([
            'created_by' => $agent->id,
            'category_id' => $category->id,
            'title' => 'Ticket van de helpdesk',
        ]);

        $this->actingAs($employee)
            ->put(route('tickets.update', $own), [
                'title' => 'Mijn ticket aangepast',
                'description' => 'Nog steeds kapot.',
                'category_id' => $category->id,
                'priority' => 'high',
                'status' => 'closed',
                'assigned_to' => $agent->id,
            ])
            ->assertRedirect(route('tickets.show', $own));

        $own->refresh();
        $this->assertSame('Mijn ticket aangepast', $own->title);
        $this->assertSame('open', $own->status);
        $this->assertNull($own->assigned_to);

        $this->actingAs($employee)->put(route('tickets.update', $foreign), [
            'title' => 'Gehackt',
            'description' => 'Dit mag niet.',
            'category_id' => $category->id,
            'priority' => 'high',
        ])->assertForbidden();

        $this->actingAs($employee)->delete(route('tickets.destroy', $foreign))->assertForbidden();
        $this->assertModelExists($foreign);

        $this->actingAs($employee)
            ->delete(route('tickets.destroy', $own))
            ->assertRedirect(route('tickets.index'));

        $this->assertModelMissing($own);
    }

    public function test_agent_can_update_and_delete_a_ticket_they_created(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $colleague = User::factory()->create(['role' => 'agent']);
        $category = Category::factory()->create();

        $ticket = Ticket::factory()->create([
            'created_by' => $agent->id,
            'category_id' => $category->id,
            'status' => 'open',
        ]);

        $this->actingAs($agent)
            ->put(route('tickets.update', $ticket), [
                'title' => 'Wifi hersteld',
                'description' => 'De verbinding doet het weer.',
                'category_id' => $category->id,
                'priority' => 'normal',
                'status' => 'closed',
                'assigned_to' => $colleague->id,
            ])
            ->assertRedirect(route('tickets.show', $ticket));

        $ticket->refresh();
        $this->assertSame('closed', $ticket->status);
        $this->assertSame($colleague->id, $ticket->assigned_to);

        $this->actingAs($agent)
            ->delete(route('tickets.destroy', $ticket))
            ->assertRedirect(route('tickets.index'));

        $this->assertModelMissing($ticket);
    }

    public function test_assigned_agent_can_close_a_ticket_created_by_an_employee(): void
    {
        $jan = User::factory()->create(['role' => 'employee', 'name' => 'Jan']);
        $piet = User::factory()->create(['role' => 'agent', 'name' => 'Piet']);
        $other = User::factory()->create(['role' => 'agent']);
        $category = Category::factory()->create();

        $ticket = Ticket::factory()->create([
            'created_by' => $jan->id,
            'assigned_to' => $piet->id,
            'category_id' => $category->id,
            'status' => 'open',
            'priority' => 'normal',
            'title' => 'Printer werkt niet',
            'description' => 'De printer op de eerste verdieping doet het niet.',
        ]);

        $this->actingAs($piet)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('>Bewerken<', false);

        $this->actingAs($piet)
            ->put(route('tickets.update', $ticket), [
                'title' => 'Printer werkt niet',
                'description' => 'De printer op de eerste verdieping doet het niet.',
                'category_id' => $category->id,
                'priority' => 'normal',
                'status' => 'closed',
                'assigned_to' => $piet->id,
            ])
            ->assertRedirect(route('tickets.show', $ticket));

        $this->assertSame('closed', $ticket->fresh()->status);

        $this->actingAs($piet)->delete(route('tickets.destroy', $ticket))->assertForbidden();
        $this->actingAs($other)->put(route('tickets.update', $ticket), [
            'title' => 'Printer werkt niet',
            'description' => 'De printer op de eerste verdieping doet het niet.',
            'category_id' => $category->id,
            'priority' => 'normal',
            'status' => 'open',
            'assigned_to' => $other->id,
        ])->assertForbidden();
    }

    public function test_agent_cannot_change_a_ticket_created_by_someone_else(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $owner = User::factory()->create(['role' => 'employee']);
        $category = Category::factory()->create();

        $ticket = Ticket::factory()->create([
            'created_by' => $owner->id,
            'category_id' => $category->id,
            'title' => 'Blijft staan',
        ]);

        $this->actingAs($agent)->put(route('tickets.update', $ticket), [
            'title' => 'Stiekem gewijzigd',
            'description' => 'Mag niet.',
            'category_id' => $category->id,
            'priority' => 'low',
            'status' => 'closed',
        ])->assertForbidden();

        $this->assertSame('Blijft staan', $ticket->fresh()->title);
    }

    public function test_admin_can_update_and_delete_any_ticket(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['role' => 'employee']);
        $category = Category::factory()->create();

        $ticket = Ticket::factory()->create([
            'created_by' => $employee->id,
            'category_id' => $category->id,
            'status' => 'open',
        ]);

        $this->actingAs($admin)->get(route('tickets.edit', $ticket))->assertOk();

        $this->actingAs($admin)
            ->put(route('tickets.update', $ticket), [
                'title' => 'Opgepakt door admin',
                'description' => 'Ik neem dit over.',
                'category_id' => $category->id,
                'priority' => 'high',
                'status' => 'in_progress',
                'assigned_to' => $admin->id,
            ])
            ->assertRedirect(route('tickets.show', $ticket));

        $this->assertSame('in_progress', $ticket->fresh()->status);

        $this->actingAs($admin)
            ->delete(route('tickets.destroy', $ticket))
            ->assertRedirect(route('tickets.index'));

        $this->assertModelMissing($ticket);
    }

    public function test_a_ticket_can_only_be_assigned_to_an_agent_or_admin(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $employee = User::factory()->create(['role' => 'employee']);
        $category = Category::factory()->create();

        $this->actingAs($agent)
            ->post(route('tickets.store'), [
                'title' => 'Printer werkt niet',
                'description' => 'Er komt niets uit.',
                'category_id' => $category->id,
                'priority' => 'normal',
                'status' => 'open',
                'assigned_to' => $employee->id,
            ])
            ->assertSessionHasErrors('assigned_to');

        $this->assertSame(0, Ticket::count());

        $ticket = Ticket::factory()->create([
            'created_by' => $agent->id,
            'assigned_to' => $agent->id,
            'category_id' => $category->id,
            'status' => 'open',
        ]);

        $this->actingAs($agent)
            ->put(route('tickets.update', $ticket), [
                'title' => $ticket->title,
                'description' => $ticket->description,
                'category_id' => $category->id,
                'priority' => 'normal',
                'status' => 'open',
                'assigned_to' => $employee->id,
            ])
            ->assertSessionHasErrors('assigned_to');

        $this->assertSame($agent->id, $ticket->fresh()->assigned_to);
    }

    public function test_the_ticket_overview_is_split_into_pages_of_fifteen(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $category = Category::factory()->create();

        Ticket::factory()->create([
            'created_by' => $agent->id,
            'category_id' => $category->id,
            'title' => 'Helemaal achteraan',
            'created_at' => now()->subDay(),
        ]);

        Ticket::factory()->count(14)->create([
            'created_by' => $agent->id,
            'category_id' => $category->id,
            'title' => 'Gewoon ticket',
            'created_at' => now()->subHour(),
        ]);

        Ticket::factory()->create([
            'created_by' => $agent->id,
            'category_id' => $category->id,
            'title' => 'Helemaal vooraan',
            'created_at' => now(),
        ]);

        $this->actingAs($agent)
            ->get(route('tickets.index'))
            ->assertOk()
            ->assertSee('Helemaal vooraan')
            ->assertDontSee('Helemaal achteraan');

        $this->actingAs($agent)
            ->get(route('tickets.index', ['mine' => 2]))
            ->assertOk()
            ->assertSee('Helemaal achteraan')
            ->assertDontSee('Helemaal vooraan');
    }

    public function test_invalid_ticket_data_is_rejected(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);

        $this->actingAs($agent)
            ->post(route('tickets.store'), [
                'title' => '',
                'description' => '',
                'category_id' => 999,
                'priority' => 'urgent',
                'status' => 'done',
            ])
            ->assertSessionHasErrors(['title', 'description', 'category_id', 'priority', 'status']);

        $this->assertSame(0, Ticket::count());
    }

    public function test_status_filter_and_unassigned_filter(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $category = Category::factory()->create();

        Ticket::factory()->create([
            'created_by' => $agent->id,
            'category_id' => $category->id,
            'assigned_to' => null,
            'status' => 'open',
            'title' => 'Nog niemand',
        ]);

        Ticket::factory()->create([
            'created_by' => $agent->id,
            'category_id' => $category->id,
            'assigned_to' => $agent->id,
            'status' => 'closed',
            'title' => 'Al afgerond',
        ]);

        $this->actingAs($agent)
            ->get(route('tickets.index', ['status' => 'open']))
            ->assertOk()
            ->assertSee('Nog niemand')
            ->assertDontSee('Al afgerond');

        $this->actingAs($agent)
            ->get(route('tickets.index', ['assigned' => 'none']))
            ->assertOk()
            ->assertSee('Nog niemand')
            ->assertDontSee('Al afgerond');
    }

    public function test_dashboard_counts_follow_the_role(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $agent = User::factory()->create(['role' => 'agent']);
        $category = Category::factory()->create();

        Ticket::factory()->create([
            'created_by' => $employee->id,
            'category_id' => $category->id,
            'status' => 'open',
            'assigned_to' => null,
            'title' => 'Ticket van medewerker',
        ]);

        Ticket::factory()->create([
            'created_by' => $agent->id,
            'category_id' => $category->id,
            'status' => 'closed',
            'title' => 'Ticket van agent',
        ]);

        $this->actingAs($employee)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('counts', ['open' => 1, 'in_progress' => 0, 'closed' => 0])
            ->assertViewHas('unassigned', null)
            ->assertSee('Ticket van medewerker')
            ->assertDontSee('Ticket van agent')
            ->assertDontSee('Niet toegewezen');

        $this->actingAs($agent)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertViewHas('counts', ['open' => 1, 'in_progress' => 0, 'closed' => 1])
            ->assertViewHas('unassigned', 1)
            ->assertSee('Niet toegewezen');
    }

    public function test_employee_can_comment_on_their_own_ticket_but_not_on_someone_elses(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $category = Category::factory()->create();

        $own = Ticket::factory()->create([
            'created_by' => $employee->id,
            'category_id' => $category->id,
        ]);

        $foreign = Ticket::factory()->create([
            'category_id' => $category->id,
        ]);

        $this->actingAs($employee)
            ->post(route('tickets.comments.store', $own), [
                'message' => 'Ik heb het opnieuw geprobeerd.',
                'is_internal' => '1',
            ])
            ->assertRedirect(route('tickets.show', $own));

        $comment = Comment::first();
        $this->assertSame('Ik heb het opnieuw geprobeerd.', $comment->message);
        $this->assertSame(0, $comment->is_internal);

        $this->actingAs($employee)
            ->post(route('tickets.comments.store', $foreign), ['message' => 'Dit mag niet.'])
            ->assertForbidden();
    }

    public function test_employee_does_not_see_internal_notes(): void
    {
        $employee = User::factory()->create(['role' => 'employee']);
        $agent = User::factory()->create(['role' => 'agent']);
        $category = Category::factory()->create();

        $ticket = Ticket::factory()->create([
            'created_by' => $employee->id,
            'category_id' => $category->id,
        ]);

        Comment::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'message' => 'Alleen voor de helpdesk',
            'is_internal' => true,
        ]);

        Comment::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
            'message' => 'Zichtbaar voor de melder',
            'is_internal' => false,
        ]);

        $this->actingAs($employee)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Zichtbaar voor de melder')
            ->assertDontSee('Alleen voor de helpdesk');

        $this->actingAs($agent)
            ->get(route('tickets.show', $ticket))
            ->assertOk()
            ->assertSee('Alleen voor de helpdesk');
    }

    public function test_agent_can_leave_an_internal_note(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create();

        $this->actingAs($agent)
            ->post(route('tickets.comments.store', $ticket), [
                'message' => 'Eerst de printer controleren.',
                'is_internal' => '1',
            ])
            ->assertRedirect(route('tickets.show', $ticket));

        $this->assertSame(1, Comment::first()->is_internal);
    }

    public function test_empty_comment_is_rejected(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create();

        $this->actingAs($agent)
            ->post(route('tickets.comments.store', $ticket), ['message' => ''])
            ->assertSessionHasErrors('message');

        $this->assertSame(0, Comment::count());
    }

    public function test_users_can_only_edit_their_own_comment(): void
    {
        $author = User::factory()->create(['role' => 'agent']);
        $other = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create();

        $comment = Comment::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $author->id,
            'message' => 'Eerste versie',
            'is_internal' => false,
        ]);

        $this->actingAs($other)
            ->put(route('tickets.comments.update', [$ticket, $comment]), ['message' => 'Gewijzigd'])
            ->assertForbidden();

        $this->assertSame('Eerste versie', $comment->fresh()->message);

        $this->actingAs($author)
            ->put(route('tickets.comments.update', [$ticket, $comment]), [
                'message' => 'Aangepaste versie',
                'is_internal' => '1',
            ])
            ->assertRedirect(route('tickets.show', $ticket));

        $comment->refresh();
        $this->assertSame('Aangepaste versie', $comment->message);
        $this->assertSame(1, $comment->is_internal);
    }

    public function test_only_the_author_or_an_admin_can_delete_a_comment(): void
    {
        $author = User::factory()->create(['role' => 'employee']);
        $agent = User::factory()->create(['role' => 'agent']);
        $admin = User::factory()->create(['role' => 'admin']);
        $ticket = Ticket::factory()->create(['created_by' => $author->id]);

        $kept = Comment::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $author->id,
            'message' => 'Blijft staan',
        ]);

        $removed = Comment::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $author->id,
            'message' => 'Weg door admin',
        ]);

        $this->actingAs($agent)
            ->delete(route('tickets.comments.destroy', [$ticket, $kept]))
            ->assertForbidden();

        $this->assertModelExists($kept);

        $this->actingAs($author)
            ->delete(route('tickets.comments.destroy', [$ticket, $kept]))
            ->assertRedirect(route('tickets.show', $ticket));

        $this->assertModelMissing($kept);

        $this->actingAs($admin)
            ->delete(route('tickets.comments.destroy', [$ticket, $removed]))
            ->assertRedirect(route('tickets.show', $ticket));

        $this->assertModelMissing($removed);
    }

    public function test_a_comment_cannot_be_changed_through_another_ticket(): void
    {
        $agent = User::factory()->create(['role' => 'agent']);
        $ticket = Ticket::factory()->create();
        $other = Ticket::factory()->create();

        $comment = Comment::factory()->create([
            'ticket_id' => $ticket->id,
            'user_id' => $agent->id,
        ]);

        $this->actingAs($agent)
            ->put(route('tickets.comments.update', [$other, $comment]), ['message' => 'Verkeerd ticket'])
            ->assertNotFound();
    }
}
