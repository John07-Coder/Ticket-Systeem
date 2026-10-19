<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Ticket;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function store(Request $request, Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $ticket->comments()->create([
            'user_id' => $request->user()->id,
            'message' => $data['message'],
            'is_internal' => $request->user()->isStaff() && $request->boolean('is_internal'),
        ]);

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Reactie geplaatst.');
    }

    public function update(Request $request, Ticket $ticket, Comment $comment)
    {
        $this->authorize('update', $comment);

        $data = $request->validate([
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $comment->message = $data['message'];

        if ($request->user()->isStaff()) {
            $comment->is_internal = $request->boolean('is_internal');
        }

        $comment->save();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Reactie bijgewerkt.');
    }

    public function destroy(Ticket $ticket, Comment $comment)
    {
        $this->authorize('delete', $comment);

        $comment->delete();

        return redirect()
            ->route('tickets.show', $ticket)
            ->with('status', 'Reactie verwijderd.');
    }
}
