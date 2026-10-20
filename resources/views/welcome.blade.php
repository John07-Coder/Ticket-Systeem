<!DOCTYPE html>
<html lang="nl" class="dark">
    <head>
        @include('partials.head', ['title' => 'IT-helpdesk', 'standaloneTitle' => true])
    </head>
    <body>
        <div class="landing" data-flux-main>
            <header class="landing-bar">
                <a class="mark" href="{{ route('home') }}">
                    <span class="mark-icon">H</span>
                    IT-helpdesk
                </a>

                <nav class="actions">
                    @auth
                        <a class="btn" href="{{ route('dashboard') }}">Dashboard</a>
                    @else
                        <a class="btn-quiet" href="{{ route('login') }}">Inloggen</a>
                        @if (Route::has('register'))
                            <a class="btn" href="{{ route('register') }}">Account aanmaken</a>
                        @endif
                    @endauth
                </nav>
            </header>

            <section class="landing-hero">
                <div class="pitch">
                    <div>
                        <p class="kicker">IT-helpdesk</p>
                        <h1>Meld het. Wij pakken het op.</h1>
                        <p class="lead">Printer stuk, account geblokkeerd of software die vastloopt. Eén ticket, een duidelijke status, en je ziet wie ermee bezig is.</p>
                    </div>

                    <ol class="pitch-points">
                        <li>
                            <strong>1 · Melden</strong>
                            <span>Beschrijf het probleem en kies een categorie en prioriteit.</span>
                        </li>
                        <li>
                            <strong>2 · Opvolgen</strong>
                            <span>Volg de status, de reacties en wie het ticket heeft overgenomen.</span>
                        </li>
                        <li>
                            <strong>3 · Afhandelen</strong>
                            <span>Een agent werkt het bij en sluit het als het opgelost is.</span>
                        </li>
                    </ol>

                    <div class="actions">
                        @auth
                            <a class="btn" href="{{ route('tickets.create') }}">Nieuw ticket</a>
                            <a class="btn-quiet" href="{{ route('tickets.index') }}">Alle tickets</a>
                        @else
                            <a class="btn" href="{{ route('login') }}">Inloggen</a>
                            @if (Route::has('register'))
                                <a class="btn-quiet" href="{{ route('register') }}">Account aanmaken</a>
                            @endif
                        @endauth
                    </div>
                </div>

                <div class="stack">
                    <article class="card card-open">
                        <p class="meta">
                            <span class="badge badge-open">Open</span>
                            <span class="badge badge-high">Hoog</span>
                            <span class="chip">Hardware</span>
                        </p>
                        <strong>Printer op de tweede verdieping</strong>
                        <p class="who who-none">Niet toegewezen</p>
                    </article>
                    <article class="card card-in_progress">
                        <p class="meta">
                            <span class="badge badge-in_progress">In behandeling</span>
                            <span class="badge badge-normal">Normaal</span>
                            <span class="chip">Netwerk</span>
                        </p>
                        <strong>VPN-verbinding valt weg</strong>
                        <p class="who">Toegewezen aan Piet</p>
                    </article>
                    <article class="card card-closed">
                        <p class="meta">
                            <span class="badge badge-closed">Gesloten</span>
                            <span class="badge badge-low">Laag</span>
                            <span class="chip">Accounts</span>
                        </p>
                        <strong>Wachtwoord opnieuw instellen</strong>
                        <p class="who">Toegewezen aan Sara</p>
                    </article>
                </div>
            </section>
        </div>
    </body>
</html>
