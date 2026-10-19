<article class="panel comment {{ $comment->is_internal ? 'is-internal' : '' }}">
    <header>
        <p>
            {{ $comment->user->name ?? 'Onbekend' }}
            · {{ $comment->created_at->format('d-m-Y H:i') }}
            @if ($comment->is_internal)
                · Intern
            @endif
        </p>

        @can('delete', $comment)
            <form method="POST" action="{{ route('tickets.comments.destroy', [$ticket, $comment]) }}" onsubmit="return confirm('Deze reactie verwijderen?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-danger">Verwijderen</button>
            </form>
        @endcan
    </header>

    <p>{{ $comment->message }}</p>

    @can('update', $comment)
        <details>
            <summary>Wijzigen</summary>

            <form method="POST" action="{{ route('tickets.comments.update', [$ticket, $comment]) }}" class="form">
                @csrf
                @method('PUT')

                <textarea name="message" rows="3" required>{{ $comment->message }}</textarea>

                @if (auth()->user()->role !== 'employee')
                    <label class="check">
                        <input type="checkbox" name="is_internal" value="1" @checked($comment->is_internal)>
                        Interne notitie
                    </label>
                @endif

                <button type="submit" class="btn-quiet">Opslaan</button>
            </form>
        </details>
    @endcan
</article>
