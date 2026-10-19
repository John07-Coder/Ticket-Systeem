<x-layouts::app :title="__('Ticket bewerken')">
    <div class="page">
        <a href="{{ route('tickets.show', $ticket) }}" class="back">Terug naar ticket</a>

        <header class="page-head">
            <div>
                <h1>Ticket bewerken</h1>
                <p class="lead">{{ $ticket->title }}</p>
            </div>
        </header>

        @include('tickets.partials.form')
    </div>
</x-layouts::app>
