<x-layouts::app :title="__('Tickets')">
    <div class="page">
        <header class="page-head">
            <div>
                <h1>Tickets</h1>
                <p class="lead">
                    @if (auth()->user()->isStaff())
                        Alle meldingen.
                    @else
                        Meldingen die jij hebt aangemaakt.
                    @endif
                </p>
            </div>

            <a href="{{ route('tickets.create') }}" class="btn">Nieuw ticket</a>
        </header>

        <nav class="pills">
            @foreach (['' => 'Alles', 'open' => 'Open', 'in_progress' => 'In behandeling', 'closed' => 'Gesloten'] as $value => $label)
                <a
                    href="{{ $value === '' ? route('tickets.index') : route('tickets.index', ['status' => $value]) }}"
                    class="pill {{ ($status ?? null) === ($value === '' ? null : $value) ? 'is-active' : '' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="panel">
            @forelse ($tickets as $ticket)
                <a href="{{ route('tickets.show', $ticket) }}" class="row row-{{ $ticket->status }}">
                    <strong>{{ $ticket->title }}</strong>
                    @include('tickets.partials.meta')
                    @if (auth()->user()->isStaff())
                        <p class="lead">Toegewezen aan {{ $ticket->assignee->name ?? 'niemand' }}</p>
                    @endif
                </a>
            @empty
                <p class="empty">Er zijn nog geen tickets.</p>
            @endforelse
        </div>
    </div>
</x-layouts::app>
