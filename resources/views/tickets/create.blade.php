<x-layouts::app :title="__('Nieuw ticket')">
    <div class="page">
        <a href="{{ route('tickets.index') }}" class="back">Terug naar tickets</a>

        <header class="page-head">
            <div>
                <h1>Nieuw ticket</h1>
                <p class="lead">Beschrijf het probleem zo duidelijk mogelijk.</p>
            </div>
        </header>

        @include('tickets.partials.form')
    </div>
</x-layouts::app>
