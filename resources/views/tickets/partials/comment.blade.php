<article class="panel comment {{ $comment->is_internal ? 'is-internal' : '' }}">
    <header>
        <div class="who-row">
            <span class="avatar">{{ strtoupper(mb_substr($comment->user->name ?? '?', 0, 1)) }}</span>
            <p>
                <strong>{{ $comment->user->name ?? 'Onbekend' }}</strong>
                <span>{{ $comment->created_at->format('d-m-Y H:i') }}</span>
                @if ($comment->is_internal)
                    <span class="badge badge-note">Intern</span>
                @endif
            </p>
        </div>

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
