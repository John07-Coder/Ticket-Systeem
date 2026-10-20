<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Ticket::class);

        $user = request()->user();

        $query = Ticket::with(['category', 'creator', 'assignee']);

        if ($user->role === 'employee') {
            $query->where('created_by', $user->id);
        }

        $status = request('status');

        if (in_array($status, ['open', 'in_progress', 'closed'])) {
            $query->where('status', $status);
        }

        if ($user->role !== 'employee' && request('assigned') === 'none') {
            $query->whereNull('assigned_to')->where('status', '!=', 'closed');
        }

        if ($user->role === 'employee') {
            $assignedTickets = collect();
            $myTickets = $query->latest()->paginate(15)->withQueryString();
            $otherTickets = collect();
        } else {
            $notMine = function ($inner) use ($user) {
                $inner->where('assigned_to', '!=', $user->id)->orWhereNull('assigned_to');
            };

            $assignedTickets = (clone $query)->where('assigned_to', $user->id)->latest()->paginate(15, ['*'], 'assigned')->withQueryString();
            $myTickets = (clone $query)->where('created_by', $user->id)->where($notMine)->latest()->paginate(15, ['*'], 'mine')->withQueryString();
            $otherTickets = (clone $query)->where('created_by', '!=', $user->id)->where($notMine)->latest()->paginate(15, ['*'], 'others')->withQueryString();
        }

        return view('tickets.index', compact('assignedTickets', 'myTickets', 'otherTickets', 'status'));
    }

    public function create(): View
    {
        $this->authorize('create', Ticket::class);

        $categories = Category::orderBy('name')->get();
        $agents = request()->user()->role === 'admin'
            ? User::whereIn('role', ['admin', 'agent'])->orderBy('name')->get()
            : collect();

        return view('tickets.create', compact('categories', 'agents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', Ticket::class);

        $user = $request->user();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'priority' => ['required', 'in:low,normal,high'],
        ];

        if ($user->role === 'admin') {
            $rules['assigned_to'] = ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', ['admin', 'agent']))];
            $rules['status'] = ['required', 'in:open,in_progress,closed'];
        } elseif ($user->role === 'agent') {
            $rules['assigned_to'] = ['nullable', function (string $attribute, mixed $value, \Closure $fail) use ($user): void {
                if ($value !== null && (int) $value !== (int) $user->id) {
                    $fail('Alleen een admin kan een ticket aan iemand anders toewijzen.');
                }
            }];
            $rules['status'] = ['required', 'in:open,in_progress,closed'];
        }

        $data = $request->validate($rules, [
            'assigned_to.exists' => 'Wijs het ticket toe aan een agent of een admin.',
        ]);

        if ($user->role === 'admin') {
            $assignedTo = $data['assigned_to'] ?? null;
        } elseif ($user->role === 'agent' && filled($data['assigned_to'] ?? null)) {
            $assignedTo = $user->id;
        } else {
            $assignedTo = null;
        }

        $ticket = DB::transaction(function () use ($user, $data, $assignedTo): Ticket {
            $ticket = Ticket::create([
                'created_by' => $user->id,
                'assigned_to' => $assignedTo,
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'description' => $data['description'],
                'status' => $user->role === 'employee' ? 'open' : $data['status'],
                'priority' => $data['priority'],
            ]);

            $ticket->log($user, 'created');

            if ($ticket->assigned_to) {
                $ticket->log($user, 'assigned', 'assigned_to', 'niemand', User::find($ticket->assigned_to)?->name);
            }

            return $ticket;
        });

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket aangemaakt.');
    }

    public function show(Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        $user = request()->user();

        $ticket->load(['category', 'creator', 'assignee', 'activities.user']);

        $comments = $ticket->comments()->with('user')->oldest();

        if ($user->role === 'employee') {
            $comments->where('is_internal', false);
        }

        $ticket->setRelation('comments', $comments->get());

        return view('tickets.show', compact('ticket'));
    }

    public function edit(Ticket $ticket): View
    {
        $this->authorize('update', $ticket);

        $categories = Category::orderBy('name')->get();
        $agents = request()->user()->role === 'admin'
            ? User::whereIn('role', ['admin', 'agent'])->orderBy('name')->get()
            : collect();

        return view('tickets.edit', compact('ticket', 'categories', 'agents'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $user = $request->user();

        $rules = [
            'category_id' => ['required', 'exists:categories,id'],
            'priority' => ['required', 'in:low,normal,high'],
        ];

        if ($user->can('updateContent', $ticket)) {
            $rules['title'] = ['required', 'string', 'max:255'];
            $rules['description'] = ['required', 'string'];
        }

        if ($user->role === 'admin') {
            $rules['assigned_to'] = ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', ['admin', 'agent']))];
            $rules['status'] = ['required', 'in:open,in_progress,closed'];
        } elseif ($user->role === 'agent') {
            $rules['assigned_to'] = ['nullable', function (string $attribute, mixed $value, \Closure $fail) use ($ticket): void {
                $incoming = $value === null || $value === '' ? null : (int) $value;
                $current = $ticket->assigned_to === null ? null : (int) $ticket->assigned_to;

                if ($incoming !== $current) {
                    $fail('Alleen een admin kan een ticket aan iemand anders toewijzen.');
                }
            }];
            $rules['status'] = ['required', 'in:open,in_progress,closed'];
        }

        $data = $request->validate($rules, [
            'assigned_to.exists' => 'Wijs het ticket toe aan een agent of een admin.',
        ]);

        $before = [
            'category' => $ticket->category?->name,
            'priority' => $ticket->priority,
            'status' => $ticket->status,
            'assigned_to' => $ticket->assigned_to,
            'assignee' => $ticket->assigned_to === null ? 'niemand' : $ticket->assignee->name,
        ];

        DB::transaction(function () use ($ticket, $user, $data, $before): void {
            if ($user->can('updateContent', $ticket)) {
                $ticket->title = $data['title'];
                $ticket->description = $data['description'];
            }

            $ticket->category_id = $data['category_id'];
            $ticket->priority = $data['priority'];

            if ($user->role === 'admin') {
                $ticket->assigned_to = $data['assigned_to'] ?? null;
                $ticket->status = $data['status'];
            } elseif ($user->role === 'agent') {
                $ticket->status = $data['status'];
            }

            $ticket->save();
            $ticket->load(['category', 'assignee']);

            if ($ticket->wasChanged('title')) {
                $ticket->log($user, 'updated', 'title');
            }

            if ($ticket->wasChanged('description')) {
                $ticket->log($user, 'updated', 'description');
            }

            if ($ticket->wasChanged('category_id')) {
                $ticket->log($user, 'updated', 'category', $before['category'], $ticket->category?->name);
            }

            if ($ticket->wasChanged('priority')) {
                $ticket->log($user, 'updated', 'priority', $before['priority'], $ticket->priority);
            }

            if ($ticket->wasChanged('status')) {
                $ticket->log($user, 'status', 'status', $before['status'], $ticket->status);
            }

            if ($ticket->wasChanged('assigned_to')) {
                $to = $ticket->assigned_to === null ? 'niemand' : $ticket->assignee->name;

                if ($before['assigned_to'] === null && (int) $ticket->assigned_to === (int) $user->id) {
                    $ticket->log($user, 'claimed', 'assigned_to', 'niemand', $to);
                } else {
                    $ticket->log($user, 'assigned', 'assigned_to', $before['assignee'], $to);
                }
            }
        });

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket bijgewerkt.');
    }

    public function claim(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('claim', $ticket);

        $user = $request->user();

        $claimed = DB::transaction(function () use ($ticket, $user): bool {
            $updated = Ticket::query()
                ->whereKey($ticket->id)
                ->whereNull('assigned_to')
                ->update(['assigned_to' => $user->id]);

            if ($updated !== 1) {
                return false;
            }

            $ticket->log($user, 'claimed', 'assigned_to', 'niemand', $user->name);

            return true;
        });

        if (! $claimed) {
            return redirect()
                ->route('tickets.show', $ticket)
                ->with('error', 'Dit ticket is intussen door iemand anders overgenomen.');
        }

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket overgenomen.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index')->with('status', 'Ticket verwijderd.');
    }
}
