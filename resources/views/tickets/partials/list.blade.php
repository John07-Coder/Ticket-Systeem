<section class="lane lane-{{ $tone }}">
    <header class="lane-head">
        <h2>{{ $heading }} <span class="count">{{ $tickets->count() }}</span></h2>
        @isset($more)
            <a href="{{ $more }}" class="back">Alles bekijken</a>
        @endisset
    </header>

    <div class="cards">
        @forelse ($tickets as $ticket)
            <a href="{{ route('tickets.show', $ticket) }}" class="card card-{{ $ticket->status }}">
                @include('tickets.partials.meta')
                <strong>{{ $ticket->title }}</strong>
                @if (auth()->user()->role !== 'employee')
                    <p class="who {{ $ticket->assignee ? '' : 'who-none' }}">
                        {{ $ticket->assignee ? 'Toegewezen aan '.$ticket->assignee->name : 'Niet toegewezen' }}
                    </p>
                @endif
            </a>
        @empty
            <p class="empty">{{ $empty }}</p>
        @endforelse
    </div>
</section>
