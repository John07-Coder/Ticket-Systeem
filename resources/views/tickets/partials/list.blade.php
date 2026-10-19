<section>
    <h2>{{ $heading }}</h2>

    <div class="panel">
        @forelse ($tickets as $ticket)
            <a href="{{ route('tickets.show', $ticket) }}" class="row row-{{ $ticket->status }}">
                <strong>{{ $ticket->title }}</strong>
                @include('tickets.partials.meta')
                @if (auth()->user()->role !== 'employee')
                    <p class="lead">Toegewezen aan {{ $ticket->assignee->name ?? 'niemand' }}</p>
                @endif
            </a>
        @empty
            <p class="empty">{{ $empty }}</p>
        @endforelse
    </div>
</section>
