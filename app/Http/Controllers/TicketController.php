<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Ticket::class);

        $status = $request->string('status')->toString();

        if (! in_array($status, ['open', 'in_progress', 'closed'], true)) {
            $status = null;
        }

        $unassigned = $request->user()->isStaff() && $request->query('assigned') === 'none';

        $tickets = Ticket::query()
            ->visibleTo($request->user())
            ->with(['category', 'creator', 'assignee'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($unassigned, fn ($query) => $query->whereNull('assigned_to')->where('status', '!=', 'closed'))
            ->latest()
            ->get();

        return view('tickets.index', [
            'tickets' => $tickets,
            'status' => $status,
            'unassigned' => $unassigned,
        ]);
    }

    public function create()
    {
        $this->authorize('create', Ticket::class);

        return view('tickets.create', $this->formData());
    }

    public function store(Request $request)
    {
        $this->authorize('create', Ticket::class);

        $data = $this->validated($request);

        $ticket = Ticket::create([
            'created_by' => $request->user()->id,
            'assigned_to' => $request->user()->isStaff() ? $data['assigned_to'] : null,
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $request->user()->isStaff() ? $data['status'] : 'open',
            'priority' => $data['priority'],
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Ticket aangemaakt.');
    }

    public function show(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $ticket->load([
            'category',
            'creator',
            'assignee',
            'comments' => function ($query) use ($request) {
                $query->with('user')->oldest();

                if (! $request->user()->isStaff()) {
                    $query->where('is_internal', false);
                }
            },
        ]);

        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        return view('tickets.edit', [
            'ticket' => $ticket,
            ...$this->formData(),
        ]);
    }

    public function update(Request $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $data = $this->validated($request);

        $ticket->fill([
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'priority' => $data['priority'],
        ]);

        if ($request->user()->isStaff()) {
            $ticket->assigned_to = $data['assigned_to'];
            $ticket->status = $data['status'];
        }

        $ticket->save();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Ticket bijgewerkt.');
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return redirect()
            ->route('tickets.index')
            ->with('status', 'Ticket verwijderd.');
    }

    /**
     * @return array{categories: \Illuminate\Database\Eloquent\Collection<int, Category>, agents: \Illuminate\Database\Eloquent\Collection<int, User>}
     */
    private function formData(): array
    {
        return [
            'categories' => Category::query()->orderBy('name')->get(),
            'agents' => User::query()->whereIn('role', ['admin', 'agent'])->orderBy('name')->get(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'priority' => ['required', Rule::in(['low', 'normal', 'high'])],
        ];

        if ($request->user()->isStaff()) {
            $request->merge([
                'assigned_to' => $request->input('assigned_to') ?: null,
            ]);

            $rules['assigned_to'] = ['nullable', 'exists:users,id'];
            $rules['status'] = ['required', Rule::in(['open', 'in_progress', 'closed'])];
        }

        return $request->validate($rules);
    }
}
