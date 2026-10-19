<x-layouts::app :title="__('Tickets')">
    <div class="page">
        <header class="page-head">
            <div>
                <h1>Tickets</h1>
                <p class="lead">
                    @if (auth()->user()->role === 'employee')
                        Dit zijn de tickets die jij hebt gemaakt.
                    @else
                        Jouw tickets staan apart van tickets die anderen hebben gemaakt.
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

        @include('tickets.partials.list', [
            'heading' => 'Jouw tickets',
            'tickets' => $myTickets,
            'empty' => 'Je hebt nog geen tickets gemaakt.',
        ])

        @if (auth()->user()->role !== 'employee')
            @include('tickets.partials.list', [
                'heading' => 'Tickets van anderen',
                'tickets' => $otherTickets,
                'empty' => 'Er zijn geen tickets van anderen.',
            ])
        @endif
    </div>
</x-layouts::app>
