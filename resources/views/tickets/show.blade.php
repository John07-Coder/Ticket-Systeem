<x-layouts::app :title="$ticket->title">
    <div class="page">
        <a href="{{ route('tickets.index') }}" class="back">Terug naar tickets</a>

        <header class="page-head">
            <div>
                <h1>{{ $ticket->title }}</h1>
                @include('tickets.partials.meta')
            </div>

            <div class="actions">
                @can('update', $ticket)
                    <a href="{{ route('tickets.edit', $ticket) }}" class="btn-quiet">Bewerken</a>
                @endcan

                @can('delete', $ticket)
                    <form method="POST" action="{{ route('tickets.destroy', $ticket) }}" onsubmit="return confirm('Dit ticket verwijderen?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-danger">Verwijderen</button>
                    </form>
                @endcan
            </div>
        </header>

        <div class="panel body">
            <p>{{ $ticket->description }}</p>
            <dl class="facts">
                <div>Aangemaakt op {{ $ticket->created_at->format('d-m-Y H:i') }}</div>
                <div>Toegewezen aan {{ $ticket->assignee->name ?? 'niemand' }}</div>
            </dl>
        </div>

        <section>
            <h2>Reacties</h2>

            <div class="comments">
                @forelse ($ticket->comments as $comment)
                    @include('tickets.partials.comment')
                @empty
                    <p class="empty">Er zijn nog geen reacties.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('tickets.comments.store', $ticket) }}" class="panel form reply">
                @csrf

                <label class="field">
                    Nieuwe reactie
                    <textarea name="message" rows="4" required>{{ old('message') }}</textarea>
                </label>

                @if (auth()->user()->isStaff())
                    <label class="check">
                        <input type="checkbox" name="is_internal" value="1" @checked(old('is_internal'))>
                        Interne notitie, niet zichtbaar voor de melder
                    </label>
                @endif

                <button type="submit" class="btn">Plaatsen</button>
            </form>
        </section>
    </div>
</x-layouts::app>
