<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketController extends Controller
{
    public function index(): View
    {
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

        $tickets = $query->latest()->get();
        $myTickets = $tickets->where('created_by', $user->id);
        $otherTickets = $tickets->where('created_by', '!=', $user->id);

        return view('tickets.index', compact('myTickets', 'otherTickets', 'status'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('name')->get();
        $agents = User::whereIn('role', ['admin', 'agent'])->orderBy('name')->get();

        return view('tickets.create', compact('categories', 'agents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'priority' => ['required', 'in:low,normal,high'],
        ];

        if ($user->role !== 'employee') {
            $rules['assigned_to'] = ['nullable', 'exists:users,id'];
            $rules['status'] = ['required', 'in:open,in_progress,closed'];
        }

        $data = $request->validate($rules);

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
        $user = request()->user();

        if ($user->role === 'employee' && $ticket->created_by !== $user->id) {
            abort(403);
        }

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
        $user = request()->user();

        if ($user->role !== 'admin' && $ticket->created_by !== $user->id && ! ($user->role === 'agent' && $ticket->assigned_to === $user->id)) {
            abort(403);
        }

        $categories = Category::orderBy('name')->get();
        $agents = User::whereIn('role', ['admin', 'agent'])->orderBy('name')->get();

        return view('tickets.edit', compact('ticket', 'categories', 'agents'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = $request->user();

        if ($user->role !== 'admin' && $ticket->created_by !== $user->id && ! ($user->role === 'agent' && $ticket->assigned_to === $user->id)) {
            abort(403);
        }

        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'category_id' => ['required', 'exists:categories,id'],
            'priority' => ['required', 'in:low,normal,high'],
        ];

        if ($user->role !== 'employee') {
            $rules['assigned_to'] = ['nullable', 'exists:users,id'];
            $rules['status'] = ['required', 'in:open,in_progress,closed'];
        }

        $data = $request->validate($rules);

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
        $user = request()->user();

        if ($user->role !== 'admin' && $ticket->created_by !== $user->id) {
            abort(403);
        }

        $ticket->delete();

        return redirect()->route('tickets.index')->with('status', 'Ticket verwijderd.');
    }
}
