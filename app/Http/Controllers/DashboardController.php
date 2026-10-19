<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $tickets = Ticket::query()->visibleTo($user);

        return view('dashboard', [
            'counts' => [
                'open' => (clone $tickets)->where('status', 'open')->count(),
                'in_progress' => (clone $tickets)->where('status', 'in_progress')->count(),
                'closed' => (clone $tickets)->where('status', 'closed')->count(),
            ],
            'unassigned' => $user->isStaff()
                ? (clone $tickets)->whereNull('assigned_to')->where('status', '!=', 'closed')->count()
                : null,
            'recent' => (clone $tickets)->with(['category', 'creator'])->latest()->limit(6)->get(),
        ]);
    }
}
