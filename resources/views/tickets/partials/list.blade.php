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
                    @if ($ticket->assigned_to !== null && (int) $ticket->assigned_to === (int) auth()->id())
                        <p class="who who-mine">Aan jou toegewezen</p>
                    @elseif ($ticket->assignee)
                        <p class="who">Toegewezen aan {{ $ticket->assignee->name }}</p>
                    @else
                        <p class="who who-none">Niet toegewezen</p>
                    @endif
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
