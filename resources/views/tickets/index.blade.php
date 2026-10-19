<x-layouts::app :title="__('Tickets')">
    <div class="page">
        <header class="page-head">
            <div>
                <h1>Tickets</h1>
                <p class="lead">
                    @if (auth()->user()->role === 'employee')
                        Dit zijn de tickets die jij hebt gemaakt.
                    @else
                        Links jouw tickets, rechts de tickets van anderen.
                    @endif
                </p>
            </div>

            <a href="{{ route('tickets.create') }}" class="btn">Nieuw ticket</a>
        </header>

        <nav class="pills">
            @foreach (['' => 'Alles', 'open' => 'Open', 'in_progress' => 'In behandeling', 'closed' => 'Gesloten'] as $value => $label)
                <a
                    href="{{ $value === '' ? route('tickets.index') : route('tickets.index', ['status' => $value]) }}"
                    class="pill pill-{{ $value === '' ? 'all' : $value }} {{ ($status ?? null) === ($value === '' ? null : $value) ? 'is-active' : '' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="board {{ auth()->user()->role === 'employee' ? '' : 'board-split' }}">
            @include('tickets.partials.list', [
                'tone' => 'mine',
                'heading' => 'Jouw tickets',
                'tickets' => $myTickets,
                'empty' => 'Je hebt nog geen tickets gemaakt.',
            ])

            @if (auth()->user()->role !== 'employee')
                @include('tickets.partials.list', [
                    'tone' => 'other',
                    'heading' => 'Tickets van anderen',
                    'tickets' => $otherTickets,
                    'empty' => 'Er zijn geen tickets van anderen.',
                ])
            @endif
        </div>
    </div>
</x-layouts::app>
