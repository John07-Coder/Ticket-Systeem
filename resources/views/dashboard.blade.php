<x-layouts::app :title="__('Dashboard')">
    <div class="page">
        <header class="page-head">
            <div>
                <h1>Dashboard</h1>
                <p class="lead">
                    @if (auth()->user()->role === 'employee')
                        Een overzicht van jouw tickets.
                    @else
                        Een overzicht van de helpdesk.
                    @endif
                </p>
            </div>

            <a href="{{ route('tickets.create') }}" class="btn">Nieuw ticket</a>
        </header>

        <div class="stats">
            @include('partials.stat', ['tone' => 'open', 'label' => 'Open', 'value' => $counts['open'], 'href' => route('tickets.index', ['status' => 'open'])])
            @include('partials.stat', ['tone' => 'progress', 'label' => 'In behandeling', 'value' => $counts['in_progress'], 'href' => route('tickets.index', ['status' => 'in_progress'])])
            @include('partials.stat', ['tone' => 'closed', 'label' => 'Gesloten', 'value' => $counts['closed'], 'href' => route('tickets.index', ['status' => 'closed'])])

            @if (auth()->user()->role !== 'employee')
                @include('partials.stat', ['tone' => 'free', 'label' => 'Niet toegewezen', 'value' => $unassigned, 'href' => route('tickets.index', ['assigned' => 'none'])])
            @endif
        </div>

        @include('tickets.partials.list', [
            'tone' => 'recent',
            'heading' => 'Recente tickets',
            'tickets' => $recent,
            'empty' => 'Er zijn nog geen tickets.',
            'more' => route('tickets.index'),
        ])
    </div>
</x-layouts::app>
