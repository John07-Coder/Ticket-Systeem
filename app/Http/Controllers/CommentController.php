<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Ticket $ticket): RedirectResponse
    {
        $user = $request->user();

        if ($user->role === 'employee' && $ticket->created_by !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $ticket->comments()->create([
            'user_id' => $user->id,
            'message' => $data['message'],
            'is_internal' => $user->role !== 'employee' && $request->boolean('is_internal'),
        ]);

        return redirect()->route('tickets.show', $ticket)->with('status', 'Reactie geplaatst.');
    }

    public function update(Request $request, Ticket $ticket, Comment $comment): RedirectResponse
    {
        $user = $request->user();

        if ($comment->user_id !== $user->id) {
            abort(403);
        }

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $comment->message = $data['message'];

        if ($user->role !== 'employee') {
            $comment->is_internal = $request->boolean('is_internal');
        }

        $comment->save();

        return redirect()->route('tickets.show', $ticket)->with('status', 'Reactie bijgewerkt.');
    }

    public function destroy(Ticket $ticket, Comment $comment): RedirectResponse
    {
        $user = request()->user();

        if ($user->role !== 'admin' && $comment->user_id !== $user->id) {
            abort(403);
        }

        $comment->delete();

        return redirect()->route('tickets.show', $ticket)->with('status', 'Reactie verwijderd.');
    }
}
