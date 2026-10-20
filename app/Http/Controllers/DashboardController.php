<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', Ticket::class);

        $user = request()->user();

        $query = Ticket::query();

        if ($user->role === 'employee') {
            $query->where('created_by', $user->id);
        }

        $counts = [
            'open' => (clone $query)->where('status', 'open')->count(),
            'in_progress' => (clone $query)->where('status', 'in_progress')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
        ];

        $unassigned = null;
        $assignedToMe = null;
        $assigned = collect();

        if ($user->role !== 'employee') {
            $unassigned = (clone $query)->whereNull('assigned_to')->where('status', '!=', 'closed')->count();
            $assignedToMe = (clone $query)->where('assigned_to', $user->id)->where('status', '!=', 'closed')->count();
            $assigned = (clone $query)
                ->where('assigned_to', $user->id)
                ->where('status', '!=', 'closed')
                ->with(['category', 'creator', 'assignee'])
                ->latest()
                ->limit(6)
                ->get();
        }

        $recent = (clone $query)->with(['category', 'creator', 'assignee'])->latest()->limit(6)->get();

        return view('dashboard', compact('counts', 'unassigned', 'assignedToMe', 'assigned', 'recent'));
    }
}
