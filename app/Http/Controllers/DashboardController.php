<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
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

        if ($user->role !== 'employee') {
            $unassigned = (clone $query)->whereNull('assigned_to')->where('status', '!=', 'closed')->count();
        }

        $recent = (clone $query)->with(['category', 'creator'])->latest()->limit(6)->get();

        return view('dashboard', compact('counts', 'unassigned', 'recent'));
    }
}
