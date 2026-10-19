<x-layouts::app :title="__('Dashboard')">
    <div class="page">
        <header class="page-head">
            <div>
                <h1>Dashboard</h1>
                <p class="lead">
                    @if (auth()->user()->isStaff())
                        Alle tickets van de helpdesk.
                    @else
                        Jouw tickets.
                    @endif
                </p>
            </div>

            <a href="{{ route('tickets.create') }}" class="btn">Nieuw ticket</a>
        </header>

        <div class="stats">
            @include('partials.stat', ['label' => 'Open', 'value' => $counts['open'], 'href' => route('tickets.index', ['status' => 'open'])])
            @include('partials.stat', ['label' => 'In behandeling', 'value' => $counts['in_progress'], 'href' => route('tickets.index', ['status' => 'in_progress'])])
            @include('partials.stat', ['label' => 'Gesloten', 'value' => $counts['closed'], 'href' => route('tickets.index', ['status' => 'closed'])])

            @if (auth()->user()->isStaff())
                @include('partials.stat', ['label' => 'Niet toegewezen', 'value' => $unassigned, 'href' => route('tickets.index', ['assigned' => 'none'])])
            @endif
        </div>

        <section class="panel">
            <div class="panel-head">
                <h2>Recente tickets</h2>
                <a href="{{ route('tickets.index') }}" class="back">Alles bekijken</a>
            </div>

            @forelse ($recent as $ticket)
                <a href="{{ route('tickets.show', $ticket) }}" class="row row-{{ $ticket->status }}">
                    <strong>{{ $ticket->title }}</strong>
                    @include('tickets.partials.meta')
                </a>
            @empty
                <p class="empty">Er zijn nog geen tickets.</p>
            @endforelse
        </section>
    </div>
</x-layouts::app>
