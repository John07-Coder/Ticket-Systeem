<x-layouts::app :title="$ticket->title">
    <div class="page">
        <a href="{{ route('tickets.index') }}" class="back">Terug naar tickets</a>

        <header class="hero hero-{{ $ticket->status }}">
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

        <div class="split">
            <article class="panel sheet">
                <h2>Beschrijving</h2>
                <p>{{ $ticket->description }}</p>
            </article>

            <aside class="panel sheet">
                <h2>Details</h2>
                <dl class="facts">
                    <div>
                        <dt>Aangemaakt</dt>
                        <dd>{{ $ticket->created_at->format('d-m-Y H:i') }}</dd>
                    </div>
                    <div>
                        <dt>Door</dt>
                        <dd>{{ $ticket->creator->name ?? 'Onbekend' }}</dd>
                    </div>
                    <div>
                        <dt>Categorie</dt>
                        <dd>{{ $ticket->category->name ?? 'Geen' }}</dd>
                    </div>
                    <div>
                        <dt>Toegewezen</dt>
                        <dd>{{ $ticket->assignee->name ?? 'Niemand' }}</dd>
                    </div>
                </dl>
            </aside>
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

                @if (auth()->user()->role !== 'employee')
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
