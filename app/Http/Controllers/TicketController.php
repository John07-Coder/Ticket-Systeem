<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            $myTickets = $query->latest()->paginate(15)->withQueryString();
            $otherTickets = collect();
        } else {
            $myTickets = (clone $query)->where('created_by', $user->id)->latest()->paginate(15, ['*'], 'mine')->withQueryString();
            $otherTickets = (clone $query)->where('created_by', '!=', $user->id)->latest()->paginate(15, ['*'], 'others')->withQueryString();
        }

        return view('tickets.index', compact('myTickets', 'otherTickets', 'status'));
    }

    public function create(): View
    {
        $this->authorize('create', Ticket::class);

        $categories = Category::orderBy('name')->get();
        $agents = User::whereIn('role', ['admin', 'agent'])->orderBy('name')->get();

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

        if ($user->role !== 'employee') {
            $rules['assigned_to'] = ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', ['admin', 'agent']))];
            $rules['status'] = ['required', 'in:open,in_progress,closed'];
        }

        $data = $request->validate($rules, [
            'assigned_to.exists' => 'Wijs het ticket toe aan een agent of een admin.',
        ]);

        $ticket = Ticket::create([
            'created_by' => $user->id,
            'assigned_to' => $user->role === 'employee' ? null : ($data['assigned_to'] ?? null),
            'category_id' => $data['category_id'],
            'title' => $data['title'],
            'description' => $data['description'],
            'status' => $user->role === 'employee' ? 'open' : $data['status'],
            'priority' => $data['priority'],
        ]);

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket aangemaakt.');
    }

    public function show(Ticket $ticket): View
    {
        $this->authorize('view', $ticket);

        $user = request()->user();

        $ticket->load(['category', 'creator', 'assignee']);

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
        $agents = User::whereIn('role', ['admin', 'agent'])->orderBy('name')->get();

        return view('tickets.edit', compact('ticket', 'categories', 'agents'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $this->authorize('update', $ticket);

        $user = $request->user();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'priority' => ['required', 'in:low,normal,high'],
        ];

        if ($user->role !== 'employee') {
            $rules['assigned_to'] = ['nullable', Rule::exists('users', 'id')->where(fn ($query) => $query->whereIn('role', ['admin', 'agent']))];
            $rules['status'] = ['required', 'in:open,in_progress,closed'];
        }

        $data = $request->validate($rules, [
            'assigned_to.exists' => 'Wijs het ticket toe aan een agent of een admin.',
        ]);

        $ticket->title = $data['title'];
        $ticket->description = $data['description'];
        $ticket->category_id = $data['category_id'];
        $ticket->priority = $data['priority'];

        if ($user->role !== 'employee') {
            $ticket->assigned_to = $data['assigned_to'] ?? null;
            $ticket->status = $data['status'];
        }

        $ticket->save();

        return redirect()->route('tickets.show', $ticket)->with('status', 'Ticket bijgewerkt.');
    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('tickets.index')->with('status', 'Ticket verwijderd.');
    }
}
