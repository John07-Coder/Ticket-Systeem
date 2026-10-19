<section class="lane lane-{{ $tone }}">
    <header class="lane-head">
        <h2>{{ $heading }} <span class="count">{{ $total ?? $tickets->count() }}</span></h2>
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
            <p class="empty">{{ ($total ?? 0) === 0 ? $empty : 'Geen tickets op deze pagina.' }}</p>
        @endforelse
    </div>

    @if ($tickets instanceof \Illuminate\Contracts\Pagination\Paginator && $tickets->hasPages())
        <div class="pager">{{ $tickets->links() }}</div>
    @endif
</section>
